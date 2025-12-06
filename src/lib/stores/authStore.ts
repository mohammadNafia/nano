import { writable } from 'svelte/store';
import type { Writable } from 'svelte/store';
import { apiClient } from '../api/client';

// MOCK USER CREDENTIALS
const MOCK_EMAIL = 'mohammadnafia1@gmail.com';
const MOCK_PASSWORD = '12345678';

export interface User {
	id: number;
	name: string;
	email: string;
	plan_type: 'FREE' | 'PRO_MONTHLY' | 'PRO_YEARLY';
	created_at?: string;
	isSubscribed?: boolean;
	plan?: string;
}

interface Subscription {
	active: boolean;
	plan: string;
	start_date: string | null;
	end_date: string | null;
}

interface AuthState {
	user: User | null;
	token: string | null;
	isAuthenticated: boolean;
	isMockUser: boolean;
	subscription: Subscription | null;
	isInitialized: boolean;
	isValidating: boolean; // Track if we're currently validating token
}

const initialState: AuthState = {
	user: null,
	token: null,
	isAuthenticated: false,
	isMockUser: false,
	subscription: null,
	isInitialized: false,
	isValidating: false
};

// Mock user data for demo account
const MOCK_USER: User = {
	id: 1,
	name: 'Mohammad Nafia',
	email: MOCK_EMAIL,
	plan_type: 'FREE',
	created_at: new Date().toISOString(),
	isSubscribed: false,
	plan: 'FREE'
};

const MOCK_SUBSCRIPTION: Subscription = {
	active: false,
	plan: 'FREE',
	start_date: new Date().toISOString(),
	end_date: null
};

function createAuthStore() {
	const { subscribe, set, update }: Writable<AuthState> = writable(initialState);
	let initPromise: Promise<void> | null = null; // Prevent multiple simultaneous initializations

	return {
		subscribe,
		
		/**
		 * Check if credentials match mock account
		 */
		isMockAccount: (email: string): boolean => {
			return email.toLowerCase() === MOCK_EMAIL.toLowerCase();
		},

		/**
		 * Validate mock login credentials
		 */
		validateMockLogin: (email: string, password: string): boolean => {
			return email.toLowerCase() === MOCK_EMAIL.toLowerCase() && password === MOCK_PASSWORD;
		},

		/**
		 * Perform mock login (for demo account only)
		 */
		mockLogin: () => {
			const mockToken = `mock_token_${Date.now()}`;
			
			if (typeof window !== 'undefined') {
				localStorage.setItem('auth_token', mockToken);
				localStorage.setItem('user', JSON.stringify(MOCK_USER));
				localStorage.setItem('isMockUser', 'true');
			}

			set({
				user: MOCK_USER,
				token: mockToken,
				isAuthenticated: true,
				isMockUser: true,
				subscription: MOCK_SUBSCRIPTION,
				isInitialized: true,
				isValidating: false
			});
		},

		/**
		 * Login with user data from backend
		 */
		login: (user: User, token: string) => {
			if (typeof window !== 'undefined') {
				localStorage.setItem('auth_token', token);
				localStorage.setItem('user', JSON.stringify(user));
				localStorage.setItem('isMockUser', 'false');
			}

			set({
				user,
				token,
				isAuthenticated: true,
				isMockUser: false,
				subscription: {
					active: user.isSubscribed || false,
					plan: user.plan || user.plan_type,
					start_date: user.created_at || null,
					end_date: null
				},
				isInitialized: true,
				isValidating: false
			});
		},

		/**
		 * Logout and clear all auth data
		 */
		logout: () => {
			if (typeof window !== 'undefined') {
				localStorage.removeItem('auth_token');
				localStorage.removeItem('user');
				localStorage.removeItem('isMockUser');
			}
			
			// Clear API cache
			apiClient.clearCache();
			
			set({ ...initialState, isInitialized: true });
		},

		/**
		 * Get current token (for API client use)
		 */
		getToken: (): string | null => {
			let token: string | null = null;
			const unsubscribe = subscribe((state) => {
				token = state.token;
			});
			unsubscribe();
			return token;
		},

		/**
		 * Initialize auth from localStorage and validate token with backend
		 * This function is idempotent - can be called multiple times safely
		 * NEVER call during SSR or module initialization
		 */
		init: async (): Promise<void> => {
			// Return existing promise if initialization is already in progress
			if (initPromise) {
				return initPromise;
			}

			if (typeof window === 'undefined') {
				// SSR - mark as initialized but not authenticated
				set({ ...initialState, isInitialized: true });
				return;
			}

			// Create and store the init promise
			initPromise = (async () => {
				try {
					// Mark as validating to prevent multiple simultaneous validations
					update((state) => ({ ...state, isValidating: true }));

					const token = localStorage.getItem('auth_token');
					const userStr = localStorage.getItem('user');
					const isMockUser = localStorage.getItem('isMockUser') === 'true';

					// If no token/user stored, mark as initialized but logged out
					if (!token || !userStr) {
						set({ ...initialState, isInitialized: true });
						initPromise = null;
						return;
					}

					// For mock users, skip backend validation
					if (isMockUser) {
						try {
							const user = JSON.parse(userStr);
							set({
								user,
								token,
								isAuthenticated: true,
								isMockUser: true,
								subscription: {
									active: user.isSubscribed || false,
									plan: user.plan || user.plan_type,
									start_date: user.created_at || null,
									end_date: null
								},
								isInitialized: true,
								isValidating: false
							});
						} catch (e) {
							console.error('Failed to parse stored mock user data:', e);
							localStorage.removeItem('auth_token');
							localStorage.removeItem('user');
							localStorage.removeItem('isMockUser');
							set({ ...initialState, isInitialized: true });
						}
						initPromise = null;
						return;
					}

					// For real users, validate token with backend
					try {
						// Parse user from storage first (optimistic update)
						let user: User;
						try {
							user = JSON.parse(userStr);
						} catch (e) {
							throw new Error('Invalid user data in storage');
						}

						// Set optimistic state
						set({
							user,
							token,
							isAuthenticated: true,
							isMockUser: false,
							subscription: {
								active: user.isSubscribed || false,
								plan: user.plan || user.plan_type,
								start_date: user.created_at || null,
								end_date: null
							},
							isInitialized: true,
							isValidating: true
						});

						// Validate token with backend (with timeout)
						try {
							const validatedUser = await Promise.race([
								apiClient.getMe(false), // Don't use cache for validation
								new Promise<User>((_, reject) => {
									setTimeout(() => reject(new Error('Validation timeout')), 5000);
								})
							]);

							// Token is valid - update with fresh user data
							set({
								user: validatedUser || user,
								token,
								isAuthenticated: true,
								isMockUser: false,
								subscription: {
									active: validatedUser?.isSubscribed || user.isSubscribed || false,
									plan: validatedUser?.plan || validatedUser?.plan_type || user.plan || user.plan_type,
									start_date: validatedUser?.created_at || user.created_at || null,
									end_date: null
								},
								isInitialized: true,
								isValidating: false
							});

							// Update localStorage with fresh data
							if (validatedUser) {
								localStorage.setItem('user', JSON.stringify(validatedUser));
							}
						} catch (validationError: any) {
							// Token validation failed - clear auth state
							console.warn('Token validation failed:', validationError);
							
							// Only clear if it's a 401 (unauthorized), not a network error
							const isUnauthorized = validationError?.response?.status === 401 || 
							                      validationError?.message?.includes('401');
							
							if (isUnauthorized) {
								// Token is invalid - clear everything
								localStorage.removeItem('auth_token');
								localStorage.removeItem('user');
								localStorage.removeItem('isMockUser');
								set({ ...initialState, isInitialized: true });
							} else {
								// Network error - keep optimistic state but mark as initialized
								set({
									user,
									token,
									isAuthenticated: true,
									isMockUser: false,
									subscription: {
										active: user.isSubscribed || false,
										plan: user.plan || user.plan_type,
										start_date: user.created_at || null,
										end_date: null
									},
									isInitialized: true,
									isValidating: false
								});
							}
						}
					} catch (e) {
						// Failed to parse or validate - clear corrupted data
						console.error('Failed to initialize auth:', e);
						localStorage.removeItem('auth_token');
						localStorage.removeItem('user');
						localStorage.removeItem('isMockUser');
						set({ ...initialState, isInitialized: true });
					}
				} catch (error) {
					console.error('Unexpected error during auth init:', error);
					set({ ...initialState, isInitialized: true });
				} finally {
					initPromise = null;
				}
			})();

			return initPromise;
		},

		/**
		 * Update user data
		 */
		updateUser: (userData: Partial<User>) => {
			update((state) => {
				if (state.user) {
					const updatedUser = { ...state.user, ...userData };
					if (typeof window !== 'undefined') {
						localStorage.setItem('user', JSON.stringify(updatedUser));
					}
					return {
						...state,
						user: updatedUser
					};
				}
				return state;
			});
		},

		/**
		 * Subscribe to a plan
		 */
		subscribeToPlan: (planName: string, isAnnual: boolean = false) => {
			update((state) => {
				if (state.user) {
					let planType: 'FREE' | 'PRO_MONTHLY' | 'PRO_YEARLY' = state.user.plan_type;
					if (planName === 'Pro' || planName === 'Premium') {
						planType = isAnnual ? 'PRO_YEARLY' : 'PRO_MONTHLY';
					}

					const updatedUser = {
						...state.user,
						isSubscribed: true,
						plan: planName,
						plan_type: planType
					};

					if (typeof window !== 'undefined') {
						localStorage.setItem('user', JSON.stringify(updatedUser));
					}

					return {
						...state,
						user: updatedUser,
						subscription: {
							active: true,
							plan: planName,
							start_date: new Date().toISOString(),
							end_date: null
						}
					};
				}
				return state;
			});
		}
	};
}

export const authStore = createAuthStore();
