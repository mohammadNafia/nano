<script lang="ts">
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import { goto } from '$app/navigation';
	import { authStore, usageStore } from '$lib/stores';
	import { apiClient } from '$lib/api';
	import { t } from '$lib/i18n';
	import MetricCard from '$lib/components/dashboard/MetricCard.svelte';
	import HistoryTable from '$lib/components/dashboard/HistoryTable.svelte';
	import ApiKeyManager from '$lib/components/dashboard/ApiKeyManager.svelte';
	import Card from '$lib/components/ui/Card.svelte';
	import Button from '$lib/components/ui/Button.svelte';

	let loading = $state(true);
	let error = $state('');
	let history = $state<any[]>([]);
	// Initialize with default values to prevent undefined errors
	let overview = $state<any>({
		uploads_this_month: 0,
		remaining_uploads: 5,
		monthly_limit: 5,
		average_confidence: null,
		last_upload_at: null,
		total_uploads: 0
	});

	onMount(async () => {
		if (!browser) {
			loading = false;
			return;
		}
		
		// Wait for auth initialization - layout already calls init(), but ensure it's complete
		await waitForAuthInitialized();
		
		// Get current auth state
		let currentAuthState: any = null;
		let authUnsub: any;
		
		await new Promise<void>((resolve) => {
			const timeout = setTimeout(() => {
				if (authUnsub) authUnsub();
				resolve();
			}, 1000);
			
			authUnsub = authStore.subscribe((state) => {
				currentAuthState = state;
				if (state.isInitialized) {
					clearTimeout(timeout);
					if (authUnsub) authUnsub();
					resolve();
				}
			});
		});
		
		// Check authentication - must be both authenticated AND initialized
		if (!currentAuthState?.isAuthenticated || !currentAuthState?.isInitialized) {
			goto('/auth', { replaceState: true });
			loading = false;
			return;
		}

		// Load data
		await loadData();
	});

	// Helper to wait for auth initialization - ensures layout's init() completes
	async function waitForAuthInitialized(): Promise<void> {
		return new Promise((resolve) => {
			let unsubscribe: any;
			const startTime = Date.now();
			const maxWait = 3000; // 3 second max wait
			
			// Check if already initialized
			unsubscribe = authStore.subscribe((state) => {
				if (state.isInitialized) {
					if (unsubscribe) unsubscribe();
					resolve();
					return;
				}
				
				// If timeout, resolve anyway
				if (Date.now() - startTime > maxWait) {
					if (unsubscribe) unsubscribe();
					resolve();
				}
			});
			
			// Force resolve after max wait
			setTimeout(() => {
				if (unsubscribe) unsubscribe();
				resolve();
			}, maxWait);
		});
	}

	async function loadData() {
		if (!browser) {
			loading = false;
			return;
		}

		loading = true;
		error = '';
		
		// Set default fallback values immediately
		const defaultOverview = {
			uploads_this_month: 0,
			remaining_uploads: 5,
			monthly_limit: 5,
			average_confidence: null,
			last_upload_at: null,
			total_uploads: 0
		};

		try {
			// Get current auth state to check if mock user
			let currentAuthState: any = null;
			let authUnsub: any;
			
			await new Promise<void>((resolve) => {
				const timeout = setTimeout(() => {
					if (authUnsub) authUnsub();
					resolve();
				}, 500);
				
				authUnsub = authStore.subscribe((state) => {
					currentAuthState = state;
					if (state.isInitialized) {
						clearTimeout(timeout);
						if (authUnsub) authUnsub();
						resolve();
					}
				});
			});
			
			// Check if user is mock - use mock data instead of API
			if (currentAuthState?.isMockUser) {
				// Mock dashboard data
				overview = {
					uploads_this_month: 3,
					remaining_uploads: 2,
					monthly_limit: 5,
					average_confidence: 0.95,
					last_upload_at: new Date().toISOString(),
					total_uploads: 3
				};

				history = [
					{
						id: 1,
						filename: 'invoice_2024.png',
						status: 'completed',
						from_cache: false,
						processing_time_ms: 1250,
						extracted_text: 'Invoice #1234\nTotal: $500.00',
						text_preview: 'Invoice #1234\nTotal: $500.00',
						created_at: new Date(Date.now() - 86400000).toISOString(),
						confidence_score: 0.95
					},
					{
						id: 2,
						filename: 'receipt.jpg',
						status: 'completed',
						from_cache: false,
						processing_time_ms: 980,
						extracted_text: 'Receipt\nAmount: $29.99',
						text_preview: 'Receipt\nAmount: $29.99',
						created_at: new Date(Date.now() - 172800000).toISOString(),
						confidence_score: 0.92
					},
					{
						id: 3,
						filename: 'document.pdf',
						status: 'completed',
						from_cache: true,
						processing_time_ms: 450,
						extracted_text: 'Sample document text...',
						text_preview: 'Sample document text...',
						created_at: new Date(Date.now() - 259200000).toISOString(),
						confidence_score: 0.98
					}
				];

				usageStore.setUsage({
					uploads_this_month: 3,
					remaining_uploads: 2,
					monthly_limit: 5,
					average_confidence: 0.95,
					last_upload_at: new Date().toISOString()
				});
				
				// Ensure loading stops
				loading = false;
				return;
			}
			
			// Real API calls for backend users
			// Add timeout to prevent hanging forever
			const API_TIMEOUT = 10000; // 10 seconds
			
			const apiPromise = Promise.all([
				apiClient.getDashboardOverview(true).catch((err) => {
					console.error('getDashboardOverview error:', err);
					return defaultOverview;
				}),
				apiClient.getDashboardHistory(1, 10, true).catch((err) => {
					console.error('getDashboardHistory error:', err);
					return [];
				})
			]);
			
			// Create timeout promise that resolves with fallback data instead of rejecting
			const timeoutPromise = new Promise<[any, any]>((resolve) => {
				setTimeout(() => {
					console.warn('Dashboard API request timed out, using fallback data');
					resolve([defaultOverview, []]);
				}, API_TIMEOUT);
			});
			
			// Race between API call and timeout
			const [overviewData, historyData] = await Promise.race([
				apiPromise,
				timeoutPromise
			]) as [any, any];

			// Ensure we have valid data
			overview = overviewData || defaultOverview;
			history = Array.isArray(historyData) ? historyData : (historyData?.data || []);

			// Update usage store
			usageStore.setUsage({
				uploads_this_month: overview.uploads_this_month || 0,
				remaining_uploads: overview.remaining_uploads ?? 5,
				monthly_limit: overview.monthly_limit ?? 5,
				average_confidence: overview.average_confidence || null,
				last_upload_at: overview.last_upload_at || null
			});
			
		} catch (err: any) {
			console.error('Failed to load dashboard data:', err);
			error = err?.message || 'Failed to load dashboard data. Showing default values.';
			
			// Always set fallback data
			overview = overview || defaultOverview;
			history = history || [];
			
			// Set usage store with defaults
			usageStore.setUsage({
				uploads_this_month: 0,
				remaining_uploads: 5,
				monthly_limit: 5,
				average_confidence: null,
				last_upload_at: null
			});
		} finally {
			// ALWAYS stop loading, even if something goes wrong
			loading = false;
		}
	}

	// Use optimized state to prevent unnecessary re-renders
	let authState = $state<{ user: any; subscription: any; isMockUser?: boolean; isAuthenticated: boolean; isInitialized: boolean }>({ 
		user: null,
		subscription: null,
		isMockUser: false, 
		isAuthenticated: false,
		isInitialized: false 
	});
	
	// Subscribe once and use derived values - only update if values changed
	authStore.subscribe((state) => {
		if (
			authState.user !== state.user ||
			authState.subscription !== state.subscription ||
			authState.isMockUser !== (state.isMockUser || false) ||
			authState.isAuthenticated !== state.isAuthenticated ||
			authState.isInitialized !== (state.isInitialized || false)
		) {
			authState = {
				user: state.user,
				subscription: state.subscription,
				isMockUser: state.isMockUser || false,
				isAuthenticated: state.isAuthenticated,
				isInitialized: state.isInitialized || false
			};
		}
	});
	
	let user = $derived(authState.user);
	let subscription = $derived(authState.subscription);

	let usageState = $state<{
		uploads_this_month: number;
		remaining_uploads: number | typeof Infinity;
		monthly_limit: number | typeof Infinity;
		average_confidence: number | null;
		last_upload_at: string | null;
	}>({
		uploads_this_month: 0,
		remaining_uploads: 0,
		monthly_limit: 5,
		average_confidence: null,
		last_upload_at: null
	});

	usageStore.subscribe((state) => {
		usageState = state;
	});
</script>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
	<div class="mb-8">
		<h1 class="text-3xl font-bold mb-2">{t('dashboard.title')}</h1>
		<p class="text-muted-foreground">{t('dashboard.welcomeBack', { name: user?.name || '' })}</p>
	</div>

	{#if loading}
		<div class="text-center py-12">
			<div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
			<p class="mt-2 text-muted-foreground">{t('dashboard.loading')}</p>
		</div>
	{:else}
		{#if error}
			<div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
				<p class="text-yellow-800 dark:text-yellow-200">{error}</p>
			</div>
		{/if}
		
		<!-- Subscription Details Section -->
		<div class="mb-8">
			<Card class="p-6">
				<h2 class="text-xl font-semibold mb-4">Subscription Details</h2>
				{#if subscription && subscription.plan}
					<div class="space-y-3">
						<div class="flex items-center justify-between flex-wrap gap-2">
							<div>
								<p class="text-sm text-muted-foreground mb-1">Current Plan</p>
								<p class="text-lg font-semibold">
									{subscription.plan === 'FREE' ? 'Free' : subscription.plan === 'PRO_MONTHLY' ? 'Pro Monthly' : subscription.plan === 'PRO_YEARLY' ? 'Pro Yearly' : subscription.plan}
								</p>
							</div>
							<div>
								{#if subscription.active}
									<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
										Active
									</span>
								{:else}
									<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
										Expired
									</span>
								{/if}
							</div>
						</div>
						
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t">
							<div>
								<p class="text-sm text-muted-foreground mb-1">Start Date</p>
								<p class="text-sm font-medium">
									{subscription.start_date 
										? new Date(subscription.start_date).toLocaleDateString('en-US', { 
											year: 'numeric', 
											month: 'long', 
											day: 'numeric' 
										})
										: '—'}
								</p>
							</div>
							<div>
								<p class="text-sm text-muted-foreground mb-1">
									{subscription.active ? 'Renews On' : 'End Date'}
								</p>
								<p class="text-sm font-medium">
									{subscription.end_date 
										? new Date(subscription.end_date).toLocaleDateString('en-US', { 
											year: 'numeric', 
											month: 'long', 
											day: 'numeric' 
										})
										: '—'}
								</p>
							</div>
						</div>
						
						{#if usageState.remaining_uploads !== Infinity && subscription.active}
							<div class="pt-3 border-t">
								<p class="text-sm text-muted-foreground mb-1">Remaining Uploads</p>
								<p class="text-sm font-medium">
									{usageState.remaining_uploads === Infinity 
										? 'Unlimited' 
										: `${usageState.remaining_uploads} / ${usageState.monthly_limit === Infinity ? '∞' : usageState.monthly_limit}`}
								</p>
							</div>
						{/if}
					</div>
				{:else}
					<div class="text-center py-4">
						<p class="text-muted-foreground">No active subscription</p>
						{#if !user?.isSubscribed && user?.plan_type === 'FREE'}
							<Button 
								variant="subscribe" 
								class="mt-4" 
								onclick={() => goto('/pricing')}
							>
								Upgrade Plan
							</Button>
						{/if}
					</div>
				{/if}
			</Card>
		</div>
		
		<!-- Always show metrics since overview is always initialized with defaults -->
		{#if overview}
		<!-- Metrics Grid -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			<MetricCard
				title={t('dashboard.uploadsThisMonth')}
				value={overview?.uploads_this_month || usageState.uploads_this_month || 0}
				subtitle={usageState.monthly_limit === Infinity ? t('dashboard.unlimited') : t('dashboard.ofAllowed', { limit: overview?.monthly_limit || usageState.monthly_limit || 5 })}
			>
				{#snippet icon()}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-6 w-6"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
						/>
					</svg>
				{/snippet}
			</MetricCard>
			<MetricCard
				title={t('dashboard.remainingUploads')}
				value={usageState.remaining_uploads === Infinity ? '∞' : (overview?.remaining_uploads ?? usageState.remaining_uploads ?? 0)}
				subtitle={usageState.remaining_uploads === Infinity ? t('dashboard.unlimited') : t('dashboard.availableThisMonth')}
			>
				{#snippet icon()}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-6 w-6"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
						/>
					</svg>
				{/snippet}
			</MetricCard>
			<MetricCard
				title={t('dashboard.averageConfidence')}
				value={overview.average_confidence ? `${Math.round(overview.average_confidence * 100)}%` : 'N/A'}
				subtitle={t('dashboard.acrossAllUploads')}
			>
				{#snippet icon()}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-6 w-6"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
						/>
					</svg>
				{/snippet}
			</MetricCard>
			<MetricCard
				title={t('dashboard.currentPlan')}
				value={user?.plan || user?.plan_type || 'FREE'}
				subtitle={user?.isSubscribed ? t('dashboard.activeSubscription') : user?.plan_type === 'FREE' ? t('dashboard.upgradeForMore') : t('dashboard.activeSubscription')}
			>
				{#snippet icon()}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-6 w-6"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
						/>
					</svg>
				{/snippet}
			</MetricCard>
		</div>

		<!-- Quick Actions -->
		<div class="mb-8">
			<Card class="p-6">
				<h2 class="text-xl font-semibold mb-4">{t('dashboard.quickActions')}</h2>
				<div class="flex flex-wrap gap-4">
					<Button variant="default" onclick={() => goto('/upload')}>{t('dashboard.uploadImage')}</Button>
					{#if user?.isSubscribed || user?.plan_type !== 'FREE'}
						<Button variant="outline" onclick={() => goto('/docs')}>{t('dashboard.viewApiDocs')}</Button>
					{/if}
					{#if !user?.isSubscribed && user?.plan_type === 'FREE'}
						<Button variant="subscribe" onclick={() => goto('/pricing')}>{t('dashboard.upgradePlan')}</Button>
					{/if}
				</div>
			</Card>
		</div>

		<!-- History -->
		<HistoryTable jobs={history} />

		<!-- API Access (Pro only) -->
		{#if user?.isSubscribed || user?.plan_type !== 'FREE'}
			<div class="mt-8">
				<ApiKeyManager />
			</div>
		{/if}
		{/if}
	{/if}
</div>
