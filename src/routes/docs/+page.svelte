<script lang="ts">
	import { goto } from '$app/navigation';
	import { t } from '$lib/i18n';
	import Button from '$lib/components/ui/Button.svelte';
	import Card from '$lib/components/ui/Card.svelte';
	import { authStore } from '$lib/stores';

	let authState = $state<{ user: any }>({ user: null });
	authStore.subscribe((state) => {
		authState = state;
	});
	let user = $derived(authState.user);
	let isSubscribed = $derived(user?.plan_type !== 'FREE' || user?.isSubscribed === true);
</script>

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

				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.authentication')}</h3>
					<p class="text-muted-foreground mb-4">
						{t('docs.authenticationDesc')}
					</p>
					<pre class="bg-muted p-4 rounded-md overflow-x-auto"><code>Authorization: Bearer YOUR_API_KEY</code></pre>
				</Card>

				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.ocrEndpoint')}</h3>
					<p class="text-muted-foreground mb-4">
						{t('docs.ocrEndpointDesc')}
					</p>
					<div class="space-y-4">
						<div>
							<p class="font-medium mb-2">POST /api/ocr</p>
							<p class="text-sm text-muted-foreground mb-2">Request:</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>Content-Type: multipart/form-data

file: [image file]</code></pre>
						</div>
						<div>
							<p class="text-sm text-muted-foreground mb-2">Response:</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "text": "Extracted text content...",
  "confidence": 0.93,
  "job_id": "uuid"
}`}</code></pre>
						</div>
					</div>
				</Card>

				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.rateLimits')}</h3>
					<ul class="list-disc list-inside space-y-2 text-muted-foreground rtl:list-inside rtl:text-right">
						<li>Free: 5 requests per month</li>
						<li>Pro Monthly: 200 requests per month</li>
						<li>Pro Yearly: 200 requests per month + 50 bonus tokens per year</li>
					</ul>
				</Card>

				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.supportedFormats')}</h3>
					<ul class="list-disc list-inside space-y-2 text-muted-foreground rtl:list-inside rtl:text-right">
						<li>PNG images</li>
						<li>JPEG/JPG images</li>
						<li>PDF documents</li>
						<li>Maximum file size: 10MB</li>
					</ul>
				</Card>

				<Card class="p-6">
					<h3 class="text-2xl font-semibold mb-4">{t('docs.errorResponses')}</h3>
					<div class="space-y-4">
						<div>
							<p class="font-medium mb-2">403 - {t('docs.limitReached')}</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "error": "${t('docs.limitReached')}",
  "message": "${t('docs.limitReachedDesc')}"
}`}</code></pre>
						</div>
						<div>
							<p class="font-medium mb-2">401 - {t('docs.unauthorized')}</p>
							<pre class="bg-muted p-4 rounded-md overflow-x-auto text-sm"><code>{`{
  "error": "${t('docs.unauthorized')}",
  "message": "${t('docs.unauthorizedDesc')}"
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
