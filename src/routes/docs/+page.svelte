<script lang="ts">
	import { goto } from '$app/navigation';
	import { t } from '$lib/i18n';
	import Button from '$lib/components/ui/Button.svelte';
	import Card from '$lib/components/ui/Card.svelte';
	import { authStore } from '$lib/stores';
	import { apiClient } from '$lib/api/client';
	import { cn } from '$lib/utils';

	let authState = $state<{ user: any }>({ user: null });
	authStore.subscribe((state) => {
		authState = state;
	});
	let user = $derived(authState.user);
	let isSubscribed = $derived(user?.plan_type !== 'FREE' || user?.isSubscribed === true);

	// State for collapsible sections
	let expandedSections = $state<Set<string>>(new Set(['overview']));
	let copiedStates = $state<Record<string, boolean>>({});

	// State for API testing
	let testStates = $state<Record<string, {
		loading: boolean;
		response: any;
		error: any;
	}>>({});

	// Test input states
	let testInputs = $state<Record<string, any>>({
		register: { name: '', email: '', password: '', password_confirmation: '' },
		login: { email: '', password: '' },
		ocrFileId: '',
		ocrHistoryLimit: '10',
		ocrUploadFile: null as File | null
	});

	const API_BASE_URL = 'http://127.0.0.1:8000/api';

	function toggleSection(id: string) {
		if (expandedSections.has(id)) {
			expandedSections.delete(id);
		} else {
			expandedSections.add(id);
		}
		expandedSections = new Set(expandedSections);
	}

	async function copyToClipboard(text: string, id: string) {
		try {
			await navigator.clipboard.writeText(text);
			copiedStates[id] = true;
			setTimeout(() => {
				copiedStates[id] = false;
			}, 2000);
		} catch (err) {
			console.error('Failed to copy:', err);
		}
	}

	// Initialize test state for an endpoint
	function initTestState(endpoint: string) {
		if (!testStates[endpoint]) {
			testStates[endpoint] = { loading: false, response: null, error: null };
		}
	}

	// Test endpoint functions
	async function testEndpoint(endpoint: string, testFn: () => Promise<any>) {
		initTestState(endpoint);
		testStates[endpoint].loading = true;
		testStates[endpoint].error = null;
		testStates[endpoint].response = null;

		try {
			const response = await testFn();
			testStates[endpoint].response = response;
		} catch (error: any) {
			testStates[endpoint].error = {
				message: error.message || 'An error occurred',
				status: error.response?.status,
				data: error.response?.data || error
			};
		} finally {
			testStates[endpoint].loading = false;
			testStates = { ...testStates };
		}
	}

	async function testOCRStatus() {
		await testEndpoint('ocr-status', () => apiClient.getOCRStatus());
	}

	async function testRateLimit() {
		await testEndpoint('rate-limit', () => apiClient.getRateLimitStatus());
	}

	async function testAuthRegister() {
		await testEndpoint('auth-register', () => 
			apiClient.testAuthRegister(testInputs.register)
		);
	}

	async function testAuthLogin() {
		await testEndpoint('auth-login', () => 
			apiClient.testAuthLogin(testInputs.login)
		);
	}

	async function testOCRUpload() {
		if (!testInputs.ocrUploadFile) {
			alert('Please select a file first');
			return;
		}
		await testEndpoint('ocr-upload', () => 
			apiClient.testOCRUpload(testInputs.ocrUploadFile!)
		);
	}

	function formatJSON(obj: any): string {
		return JSON.stringify(obj, null, 2);
	}

	function handleFileSelect(event: Event) {
		const target = event.target as HTMLInputElement;
		if (target.files && target.files[0]) {
			testInputs.ocrUploadFile = target.files[0];
		}
	}
</script>

<svelte:head>
	<title>API Documentation - OCR Service</title>
	<meta name="description" content="Complete API documentation for the OCR Service. Learn how to integrate text extraction from images into your applications." />
</svelte:head>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
	<!-- Hero Section -->
	<section class="text-center py-12 mb-16">
		<p class="text-sm font-medium text-primary mb-4 uppercase tracking-wide">{t('home.tagline')}</p>
		<h1 class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent">
			{t('home.title')}
		</h1>
		<p class="text-xl text-muted-foreground max-w-2xl mx-auto mb-8">
			{t('home.subtitle')}
		</p>
		<div class="mb-6">
			<div class="inline-flex items-center gap-3 px-6 py-3 bg-muted rounded-xl">
				<span class="text-sm text-muted-foreground font-medium">Base URL</span>
				<code class="text-primary font-mono text-sm">{API_BASE_URL}</code>
				<button
					onclick={() => copyToClipboard(API_BASE_URL, 'base-url')}
					class="p-1.5 hover:bg-background rounded-lg transition-colors"
				>
					{#if copiedStates['base-url']}
						<svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
						</svg>
					{:else}
						<svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
						</svg>
					{/if}
				</button>
			</div>
		</div>
		{#if !isSubscribed}
			<div class="flex items-center justify-center gap-4">
				<Button 
					size="lg" 
					variant="subscribe" 
					class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white shadow-lg hover:shadow-xl transition-all"
					onclick={() => goto('/dashboard')}
				>
					{t('docs.getApiAccess')}
				</Button>
				<Button size="lg" variant="outline" onclick={() => goto('/pricing')}>
					{t('docs.viewPlans')}
				</Button>
			</div>
		{/if}
	</section>

	<!-- How it Works Section -->
	<section class="py-12 mb-16">
		<h2 class="text-3xl font-bold text-center mb-12">{t('home.howItWorks')}</h2>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
			<div class="text-center">
				<div class="w-16 h-16 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
					<span class="text-2xl font-bold text-primary">1</span>
				</div>
				<h4 class="text-xl font-semibold mb-2">{t('home.step1Title')}</h4>
				<p class="text-muted-foreground">
					{t('home.step1Desc')}
				</p>
			</div>
			<div class="text-center">
				<div class="w-16 h-16 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
					<span class="text-2xl font-bold text-primary">2</span>
				</div>
				<h4 class="text-xl font-semibold mb-2">{t('home.step2Title')}</h4>
				<p class="text-muted-foreground">
					{t('home.step2Desc')}
				</p>
			</div>
			<div class="text-center">
				<div class="w-16 h-16 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
					<span class="text-2xl font-bold text-primary">3</span>
				</div>
				<h4 class="text-xl font-semibold mb-2">{t('home.step3Title')}</h4>
				<p class="text-muted-foreground">
					{t('home.step3Desc')}
				</p>
			</div>
		</div>
	</section>

	<!-- Benefits Section -->
	<section class="py-12 mb-16">
		<h2 class="text-3xl font-bold text-center mb-12">{t('home.whyTitle')}</h2>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			<Card class="p-6 hover:shadow-lg transition-shadow">
				<div class="text-center">
					<div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-6 w-6 text-primary"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M13 10V3L4 14h7v7l9-11h-7z"
							/>
						</svg>
					</div>
					<h4 class="text-xl font-semibold mb-2">{t('home.speed')}</h4>
					<p class="text-muted-foreground">
						{t('home.speedDesc')}
					</p>
				</div>
			</Card>
			<Card class="p-6 hover:shadow-lg transition-shadow">
				<div class="text-center">
					<div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-6 w-6 text-primary"
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
					</div>
					<h4 class="text-xl font-semibold mb-2">{t('home.accuracy')}</h4>
					<p class="text-muted-foreground">
						{t('home.accuracyDesc')}
					</p>
				</div>
			</Card>
			<Card class="p-6 hover:shadow-lg transition-shadow">
				<div class="text-center">
					<div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-6 w-6 text-primary"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
							/>
						</svg>
					</div>
					<h4 class="text-xl font-semibold mb-2">{t('home.privacy')}</h4>
					<p class="text-muted-foreground">
						{t('home.privacyDesc')}
					</p>
				</div>
			</Card>
		</div>
	</section>

	<!-- Documentation Section -->
	<div class="max-w-4xl mx-auto">
		<div class="border-t pt-16 mt-16">
			<h2 class="text-4xl font-bold mb-8">{t('docs.title')}</h2>

			<div class="space-y-8">
				<!-- Getting Started -->
				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.gettingStarted')}</h3>
					<p class="text-muted-foreground mb-4">
						{t('docs.gettingStartedDesc')}
					</p>
					{#if user?.plan_type === 'FREE'}
						<div class="p-4 bg-muted rounded-md mb-4">
							<p class="text-sm">
								{t('docs.apiKeysAvailable')} <a href="/pricing" class="text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded px-1">{t('docs.upgradePlan')}</a> {t('docs.toGetApiAccess')}
							</p>
						</div>
					{/if}
				</Card>

				<!-- Authentication -->
				<Card class="p-6">
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-2xl font-semibold">{t('docs.authentication')}</h3>
						<button
							onclick={() => toggleSection('auth')}
							class="p-2 hover:bg-muted rounded-lg transition-colors"
						>
							<svg class={cn("w-5 h-5 transition-transform", expandedSections.has('auth') && "rotate-180")} fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
							</svg>
						</button>
					</div>
					<p class="text-muted-foreground mb-4">
						{t('docs.authenticationDesc')}
					</p>
					<pre class="bg-muted p-4 rounded-md overflow-x-auto mb-4"><code>Authorization: Bearer YOUR_API_KEY</code></pre>
					
					{#if expandedSections.has('auth')}
						<div class="mt-4 space-y-4 pt-4 border-t">
							<!-- Test Login -->
							<div class="bg-muted/50 p-4 rounded-lg">
								<h4 class="font-semibold mb-3">Test Login Endpoint</h4>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
									<input
										type="email"
										bind:value={testInputs.login.email}
										placeholder="Email"
										class="px-3 py-2 border rounded-md text-sm"
									/>
									<input
										type="password"
										bind:value={testInputs.login.password}
										placeholder="Password"
										class="px-3 py-2 border rounded-md text-sm"
									/>
								</div>
								<button
									onclick={testAuthLogin}
									disabled={testStates['auth-login']?.loading}
									class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors disabled:opacity-50"
								>
									{testStates['auth-login']?.loading ? 'Testing...' : 'Test Login'}
								</button>
								{#if testStates['auth-login']?.response}
									<pre class="mt-3 bg-background p-3 rounded text-xs overflow-x-auto border border-primary/20"><code>{formatJSON(testStates['auth-login'].response)}</code></pre>
								{/if}
								{#if testStates['auth-login']?.error}
									<pre class="mt-3 bg-destructive/10 p-3 rounded text-xs overflow-x-auto border border-destructive/20 text-destructive"><code>{formatJSON(testStates['auth-login'].error)}</code></pre>
								{/if}
							</div>
						</div>
					{/if}
				</Card>

				<!-- OCR Endpoint -->
				<Card class="p-6">
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-2xl font-semibold">{t('docs.ocrEndpoint')}</h3>
						<button
							onclick={() => toggleSection('ocr')}
							class="p-2 hover:bg-muted rounded-lg transition-colors"
						>
							<svg class={cn("w-5 h-5 transition-transform", expandedSections.has('ocr') && "rotate-180")} fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
							</svg>
						</button>
					</div>
					<p class="text-muted-foreground mb-4">
						{t('docs.ocrEndpointDesc')}
					</p>
					<div class="space-y-4">
						<div>
							<p class="font-medium mb-2">POST {API_BASE_URL}/ocr/upload</p>
							<p class="text-sm text-muted-foreground mb-2">Request:</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>Content-Type: multipart/form-data

image: [image file]</code></pre>
						</div>
						<div>
							<p class="text-sm text-muted-foreground mb-2">Response:</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "success": true,
  "data": {
    "file_id": 42,
    "original_filename": "document.png",
    "extracted_text": "Hello World...",
    "from_cache": false,
    "processed_at": "2025-01-15T10:30:00.000000Z"
  }
}`}</code></pre>
						</div>
					</div>

					{#if expandedSections.has('ocr')}
						<div class="mt-4 pt-4 border-t">
							<!-- Test OCR Upload -->
							<div class="bg-muted/50 p-4 rounded-lg">
								<h4 class="font-semibold mb-3">Test OCR Upload</h4>
								<input
									type="file"
									accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
									onchange={handleFileSelect}
									class="w-full mb-3 text-sm"
								/>
								{#if testInputs.ocrUploadFile}
									<p class="text-xs text-muted-foreground mb-3">Selected: {testInputs.ocrUploadFile.name}</p>
								{/if}
								<button
									onclick={testOCRUpload}
									disabled={testStates['ocr-upload']?.loading || !testInputs.ocrUploadFile}
									class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors disabled:opacity-50"
								>
									{testStates['ocr-upload']?.loading ? 'Processing...' : 'Test Upload'}
								</button>
								{#if testStates['ocr-upload']?.response}
									<pre class="mt-3 bg-background p-3 rounded text-xs overflow-x-auto border border-primary/20"><code>{formatJSON(testStates['ocr-upload'].response)}</code></pre>
								{/if}
								{#if testStates['ocr-upload']?.error}
									<pre class="mt-3 bg-destructive/10 p-3 rounded text-xs overflow-x-auto border border-destructive/20 text-destructive"><code>{formatJSON(testStates['ocr-upload'].error)}</code></pre>
								{/if}
							</div>

							<!-- Test Status -->
							<div class="bg-muted/50 p-4 rounded-lg mt-4">
								<h4 class="font-semibold mb-3">Test Service Status</h4>
								<button
									onclick={testOCRStatus}
									disabled={testStates['ocr-status']?.loading}
									class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors disabled:opacity-50"
								>
									{testStates['ocr-status']?.loading ? 'Testing...' : 'Test Status'}
								</button>
								{#if testStates['ocr-status']?.response}
									<pre class="mt-3 bg-background p-3 rounded text-xs overflow-x-auto border border-primary/20"><code>{formatJSON(testStates['ocr-status'].response)}</code></pre>
								{/if}
								{#if testStates['ocr-status']?.error}
									<pre class="mt-3 bg-destructive/10 p-3 rounded text-xs overflow-x-auto border border-destructive/20 text-destructive"><code>{formatJSON(testStates['ocr-status'].error)}</code></pre>
								{/if}
							</div>
						</div>
					{/if}
				</Card>

				<!-- Rate Limits -->
				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.rateLimits')}</h3>
					<ul class="list-disc list-inside space-y-2 text-muted-foreground rtl:list-inside rtl:text-right">
						<li>Free: 5 requests per month</li>
						<li>Pro Monthly: 200 requests per month</li>
						<li>Pro Yearly: 200 requests per month + 50 bonus tokens per year</li>
					</ul>
				</Card>

				<!-- Supported Formats -->
				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.supportedFormats')}</h3>
					<ul class="list-disc list-inside space-y-2 text-muted-foreground rtl:list-inside rtl:text-right">
						<li>PNG images</li>
						<li>JPEG/JPG images</li>
						<li>GIF images</li>
						<li>WebP images</li>
						<li>Maximum file size: 10MB</li>
					</ul>
				</Card>

				<!-- Error Responses -->
				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.errorResponses')}</h3>
					<div class="space-y-4">
						<div>
							<p class="font-medium mb-2">403 - {t('docs.limitReached')}</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "success": false,
  "error": "${t('docs.limitReached')}",
  "message": "${t('docs.limitReachedDesc')}"
}`}</code></pre>
						</div>
						<div>
							<p class="font-medium mb-2">401 - {t('docs.unauthorized')}</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "success": false,
  "error": "${t('docs.unauthorized')}",
  "message": "${t('docs.unauthorizedDesc')}"
}`}</code></pre>
						</div>
						<div>
							<p class="font-medium mb-2">429 - Rate Limit Exceeded</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "rate_limit": {
    "remaining_attempts": 0,
    "blocked_until": "2025-01-15T11:30:00.000000Z"
  }
}`}</code></pre>
						</div>
					</div>
				</Card>
			</div>
		</div>
	</div>

	<!-- CTA Section -->
	{#if !isSubscribed}
		<section class="py-16 text-center mt-16">
			<Card class="p-12 bg-gradient-to-r from-blue-600/10 to-purple-600/10 border-primary/20">
				<h2 class="text-3xl font-bold mb-4">{t('home.readyTitle')}</h2>
				<p class="text-muted-foreground mb-8 max-w-xl mx-auto">
					{t('home.readyDesc')}
				</p>
				<Button 
					size="lg" 
					variant="subscribe" 
					class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white shadow-lg hover:shadow-xl transition-all"
					onclick={() => goto('/upload')}
				>
					{t('docs.startNow')}
				</Button>
			</Card>
		</section>
	{/if}
</div>
