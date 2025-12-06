import axios, { type AxiosInstance, type AxiosError } from 'axios';
import { browser } from '$app/environment';
import { authStore } from '../stores/authStore';

// Use Laravel backend API
const API_BASE_URL = 'https://ocr-main-ddl4dg.laravel.cloud/api';

// Cache interface for API responses
interface CacheEntry<T> {
	data: T;
	timestamp: number;
}

class ApiClient {
	private client: AxiosInstance;
	// Cache with TTL (Time To Live)
	private cache: Map<string, CacheEntry<any>> = new Map();
	private pendingRequests: Map<string, Promise<any>> = new Map();
	private isHandling401: boolean = false; // Prevent multiple simultaneous 401 handlers
	
	private readonly CACHE_TTL = {
		// Cache user/me for 5 minutes
		USER: 5 * 60 * 1000,
		// Cache dashboard overview for 2 minutes
		DASHBOARD_OVERVIEW: 2 * 60 * 1000,
		// Cache subscription info for 10 minutes
		SUBSCRIPTION: 10 * 60 * 1000,
		// Cache history for 1 minute
		HISTORY: 1 * 60 * 1000
	};

	constructor() {
		this.client = axios.create({
			baseURL: API_BASE_URL,
			headers: {
				'Content-Type': 'application/json',
				Accept: 'application/json'
			},
			timeout: 30000 // 30 seconds for OCR processing
		});

		// Request interceptor to add auth token from authStore
		this.client.interceptors.request.use(
			(config) => {
				if (browser) {
					// Get token from authStore state instead of localStorage directly
					const token = authStore.getToken();
					
					if (token) {
						config.headers.Authorization = `Bearer ${token}`;
					} else {
						// Ensure headers object exists
						if (!config.headers) {
							config.headers = {} as any;
						}
						// Don't send Authorization header if no token
						delete config.headers.Authorization;
					}
				}
				return config;
			},
			(error) => {
				return Promise.reject(error);
			}
		);

		// Response interceptor to handle Laravel response format and errors
		this.client.interceptors.response.use(
			(response) => {
				// Laravel returns {success, message, data, ...}
				// Extract the relevant data
				return response.data;
			},
			async (error: AxiosError) => {
				if (browser) {
					console.error('API Error:', {
						message: error.message,
						status: error.response?.status,
						data: error.response?.data,
						url: error.config?.url
					});
				}
				
				// Handle 401 Unauthorized - logout and redirect (only once)
				if (error.response?.status === 401 && browser && !this.isHandling401) {
					this.isHandling401 = true;
					
					try {
						// Only logout if we're not already handling a 401
						authStore.logout();
						
						// Only redirect if we're not already on the auth page
						if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/auth')) {
							// Use replace to prevent redirect loop
							window.location.replace('/auth');
						}
					} catch (err) {
						console.error('Error handling 401:', err);
					} finally {
						// Reset flag after a delay to allow navigation
						setTimeout(() => {
							this.isHandling401 = false;
						}, 1000);
					}
				}

				// Transform Laravel validation errors
				const errorData = error.response?.data as any;
				if (errorData?.errors) {
					// Get first validation error message
					const firstError = Object.values(errorData.errors)[0];
					const errorMessage = Array.isArray(firstError) ? firstError[0] : 'Validation failed';
					throw new Error(errorMessage);
				}

				// Use Laravel's message if available
				if (errorData?.message) {
					throw new Error(errorData.message);
				}

				// Default error message
				throw new Error('Something went wrong. Please try again.');
			}
		);
	}

	/**
	 * Get cached data if still valid, otherwise return null
	 */
	private getCached<T>(key: string, ttl: number): T | null {
		const entry = this.cache.get(key);
		if (!entry) return null;
		
		const now = Date.now();
		if (now - entry.timestamp > ttl) {
			this.cache.delete(key);
			return null;
		}
		
		return entry.data as T;
	}

	/**
	 * Set cache entry
	 */
	private setCache<T>(key: string, data: T): void {
		this.cache.set(key, {
			data,
			timestamp: Date.now()
		});
	}

	/**
	 * Clear cache for a specific key or all cache
	 */
	clearCache(key?: string): void {
		if (key) {
			this.cache.delete(key);
		} else {
			this.cache.clear();
		}
	}

	/**
	 * Request deduplication - prevents multiple identical requests
	 */
	private async dedupeRequest<T>(key: string, fn: () => Promise<T>): Promise<T> {
		// If request is already pending, return the pending promise
		if (this.pendingRequests.has(key)) {
			return this.pendingRequests.get(key)! as Promise<T>;
		}

		// Create new request
		const promise = fn().finally(() => {
			// Remove from pending once complete
			this.pendingRequests.delete(key);
		});

		this.pendingRequests.set(key, promise);
		return promise;
	}

	// Auth endpoints - matching Laravel API
	async signup(data: { name: string; email: string; password: string; password_confirmation: string }) {
		try {
			const response: any = await this.client.post('/auth/register', data);
			return {
				user: response.user || response.data?.user,
				token: response.token || response.data?.token
			};
		} catch (error: any) {
			throw error;
		}
	}

	async login(data: { email: string; password: string }) {
		try {
			const response: any = await this.client.post('/auth/login', data);
			return {
				user: response.user || response.data?.user,
				token: response.token || response.data?.token
			};
		} catch (error: any) {
			throw error;
		}
	}

	async logout() {
		try {
			const response = await this.client.post('/auth/logout');
			// Clear cache on logout
			this.clearCache();
			return response;
		} catch (error: any) {
			// Even if logout fails, clear local cache
			this.clearCache();
			// Don't throw - allow logout to complete
			console.error('Logout error:', error);
			return {};
		}
	}

	async getMe(useCache: boolean = true) {
		const cacheKey = 'auth/me';
		
		// Check cache first
		if (useCache) {
			const cached = this.getCached<any>(cacheKey, this.CACHE_TTL.USER);
			if (cached) return cached;
		}

		// Use deduplication to prevent multiple simultaneous requests
		return this.dedupeRequest(cacheKey, async () => {
			try {
				const response: any = await this.client.get('/auth/me');
				// Handle different response shapes
				const user = response?.user || response?.data?.user || response;
				
				if (user && typeof user === 'object') {
					this.setCache(cacheKey, user);
					return user;
				}
				
				throw new Error('Invalid user data received');
			} catch (error: any) {
				// Clear cache on error
				this.clearCache(cacheKey);
				throw error;
			}
		});
	}

	async refreshToken() {
		try {
			const response: any = await this.client.post('/auth/refresh');
			return {
				token: response?.token || response?.data?.token
			};
		} catch (error: any) {
			console.error('refreshToken error:', error);
			throw error;
		}
	}

	// Checkout endpoint
	async createCheckout(planType: 'PRO_MONTHLY' | 'PRO_YEARLY') {
		try {
			const response: any = await this.client.post('/checkout/create', {
				plan_type: planType
			});
			return {
				checkout_url: response?.checkout_url || response?.data?.checkout_url || null
			};
		} catch (error: any) {
			console.error('createCheckout error:', error);
			throw error;
		}
	}

	// OCR endpoints - matching Laravel API
	async ocr(file: File) {
		try {
			const formData = new FormData();
			formData.append('image', file); // Laravel expects 'image' field
			
			const response = await this.client.post('/ocr/upload', formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			});
			
			// Response interceptor already extracts response.data
			const data = response?.data || response;
			
			return {
				file_id: data?.file_id || null,
				original_filename: data?.original_filename || file.name,
				extracted_text: data?.extracted_text || '',
				from_cache: data?.from_cache || false,
				processed_at: data?.processed_at || new Date().toISOString(),
				processing_time_ms: data?.processing_time_ms || 0
			};
		} catch (error: any) {
			console.error('OCR upload error:', error);
			throw error;
		}
	}

	async getOCRResult(id: number) {
		try {
			const response = await this.client.get(`/ocr/result/${id}`);
			// Response interceptor already extracts response.data
			return response?.data || response;
		} catch (error: any) {
			console.error('getOCRResult error:', error);
			throw error;
		}
	}

	async getOCRHistory(page: number = 1, limit: number = 10) {
		try {
			const response = await this.client.get('/ocr/history', {
				params: { page, per_page: limit }
			});
			// Response interceptor already extracts response.data, so response here IS the data
			// Handle both array and object with data property
			const history = Array.isArray(response) 
				? response 
				: (response?.data || response || []);
			
			return Array.isArray(history) ? history : [];
		} catch (error: any) {
			console.error('getOCRHistory error:', error);
			// Return empty array instead of throwing to prevent UI crashes
			return [];
		}
	}

	// Dashboard endpoints
	async getDashboardOverview(useCache: boolean = true) {
		const cacheKey = 'dashboard/overview';
		
		// Check cache first
		if (useCache) {
			const cached = this.getCached<any>(cacheKey, this.CACHE_TTL.DASHBOARD_OVERVIEW);
			if (cached) return cached;
		}

		// Use deduplication
		return this.dedupeRequest(cacheKey, async () => {
			// Since backend doesn't have a dedicated overview endpoint,
			// we'll calculate stats from history
			try {
				const historyResponse: any = await this.client.get('/ocr/history', {
					params: { page: 1, per_page: 100 }
				});
				
				// Response interceptor already extracts response.data, so historyResponse IS the data
				// Handle both array and object with data property
				const history = Array.isArray(historyResponse) 
					? historyResponse 
					: (historyResponse?.data || historyResponse || []);
				
				const historyArray = Array.isArray(history) ? history : [];
				const uploads_this_month = historyArray.length;
				
				// Calculate average confidence if available
				let average_confidence: number | null = null;
				if (historyArray.length > 0) {
					const confidences = historyArray
						.map((item: any) => item.confidence_score || item.confidence)
						.filter((score: any) => typeof score === 'number' && !isNaN(score));
					
					if (confidences.length > 0) {
						const total = confidences.reduce((sum: number, score: number) => sum + score, 0);
						average_confidence = total / confidences.length;
					}
				}
				
				// Get user's plan limit from authStore
				let monthlyLimit = 5; // Default free tier
				try {
					let currentUser: any = null;
					const unsub = authStore.subscribe((state) => {
						if (state.user) {
							currentUser = state.user;
						}
					});
					unsub();
					
					if (currentUser?.plan_type === 'PRO_MONTHLY' || currentUser?.plan_type === 'PRO_YEARLY') {
						monthlyLimit = 200;
					} else if (currentUser?.isSubscribed) {
						monthlyLimit = 200;
					}
				} catch (e) {
					// Default to free tier on error
				}
				
				const result = {
					uploads_this_month: uploads_this_month,
					remaining_uploads: Math.max(0, monthlyLimit - uploads_this_month),
					monthly_limit: monthlyLimit,
					average_confidence,
					last_upload_at: historyArray.length > 0 ? (historyArray[0].created_at || historyArray[0].processed_at || null) : null,
					total_uploads: uploads_this_month
				};
				
				this.setCache(cacheKey, result);
				return result;
			} catch (error: any) {
				console.error('Dashboard overview error:', error);
				
				// If it's a 401, don't cache - let it fail properly
				if (error?.response?.status === 401) {
					throw error;
				}
				
				// Return fallback data so dashboard doesn't hang
				const fallback = {
					uploads_this_month: 0,
					remaining_uploads: 5,
					monthly_limit: 5,
					average_confidence: null,
					last_upload_at: null,
					total_uploads: 0
				};
				
				// Cache fallback for a short time to prevent rapid retries
				this.setCache(cacheKey, fallback);
				return fallback;
			}
		});
	}

	async getDashboardHistory(page: number = 1, perPage: number = 10, useCache: boolean = true) {
		const cacheKey = `dashboard/history/${page}/${perPage}`;
		
		// Only cache first page
		if (useCache && page === 1) {
			const cached = this.getCached<any[]>(cacheKey, this.CACHE_TTL.HISTORY);
			if (cached) return cached;
		}

		return this.dedupeRequest(cacheKey, async () => {
			try {
				const result = await this.getOCRHistory(page, perPage);
				// Ensure result is an array - handle both direct array and wrapped responses
				const historyArray = Array.isArray(result) ? result : (result?.data || []);
				
				if (page === 1) {
					this.setCache(cacheKey, historyArray);
				}
				return historyArray;
			} catch (error) {
				console.error('getDashboardHistory error:', error);
				// Return empty array on error instead of throwing
				return [];
			}
		});
	}

	/**
	 * Invalidate cache when user uploads a new file or makes changes
	 */
	invalidateDashboardCache(): void {
		this.clearCache('dashboard/overview');
		// Clear all history cache entries
		for (const key of this.cache.keys()) {
			if (key.startsWith('dashboard/history/')) {
				this.cache.delete(key);
			}
		}
	}

	// API Key management endpoints
	async getApiKeys() {
		try {
			const response: any = await this.client.get('/api-keys');
			return {
				data: Array.isArray(response) ? response : (response?.data || response || [])
			};
		} catch (error: any) {
			console.error('getApiKeys error:', error);
			// Return empty array on error instead of throwing
			return { data: [] };
		}
	}

	async createApiKey(label?: string) {
		try {
			const response: any = await this.client.post('/api-keys', { label });
			return {
				key: response?.key || response?.data?.key || null
			};
		} catch (error: any) {
			console.error('createApiKey error:', error);
			throw error;
		}
	}

	async deleteApiKey(id: number) {
		try {
			const response = await this.client.delete(`/api-keys/${id}`);
			return response;
		} catch (error: any) {
			console.error('deleteApiKey error:', error);
			throw error;
		}
	}
}

// Export singleton instance
export const apiClient = new ApiClient();

// Also export the class for testing
export { ApiClient };
