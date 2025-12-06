<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { authStore, langStore } from '$lib/stores';
	import { apiClient } from '$lib/api';
	import { t } from '$lib/i18n';
	import PriceCard from './PriceCard.svelte';
	import BillingToggle from './BillingToggle.svelte';
	import PriceOrbs from './PriceOrbs.svelte';

	let isAnnual = $state(false);
	let loading = $state(false);
	let currentPlan = $state<string | null>(null);
	let mounted = $state(false);
	let currentLang = $state<'en' | 'ar'>('en');

	// Subscribe to language changes to trigger plan updates
	langStore.subscribe((lang) => {
		currentLang = lang;
	});

	onMount(() => {
		mounted = true;
		authStore.subscribe((state) => {
			if (state.isAuthenticated && state.user) {
				currentPlan = state.user.plan_type;
			}
		});
	});

	interface PricingPlan {
		name: string;
		description: string;
		monthlyPrice: number;
		annualPrice: number;
		features: string[];
		highlighted: boolean;
		cta: string;
		planType?: string;
		hideInAnnual?: boolean;
	}

	// Make plans reactive to language changes
	const plans = $derived.by(() => {
		// Access currentLang to make this reactive
		const _ = currentLang;
		return [
			{
				name: t('pricing.freeTrial'),
				description: t('pricing.freeTrialDesc'),
				monthlyPrice: 0,
				annualPrice: 0,
				features: [
					t('pricing.features.freeUploads', { count: 5 }),
					t('pricing.features.basicOcr'),
					t('pricing.features.limitedHistory', { count: 5 }),
					t('pricing.features.emailSupport')
				],
				highlighted: false,
				cta: t('pricing.startFree'),
				planType: 'FREE',
				hideInAnnual: true
			},
			{
				name: t('pricing.pro'),
				description: t('pricing.proDesc'),
				monthlyPrice: 9.99,
				annualPrice: 7.99,
				features: [
					t('pricing.features.freeUploads', { count: 200 }),
					t('pricing.features.highAccuracy'),
					t('pricing.features.fullHistory'),
					t('pricing.features.apiKey'),
					t('pricing.features.dashboardAnalytics')
				],
				highlighted: true,
				cta: t('pricing.upgradeToPro'),
				planType: 'PRO_MONTHLY'
			},
			{
				name: t('pricing.premium'),
				description: t('pricing.premiumDesc'),
				monthlyPrice: 29.99,
				annualPrice: 24.99,
				features: [
					t('pricing.features.freeUploads', { count: 1000 }),
					t('pricing.features.advancedOcr'),
					t('pricing.features.unlimitedHistory'),
					t('pricing.features.priorityApi'),
					t('pricing.features.teamSeats', { count: 5 }),
					t('pricing.features.prioritySupport')
				],
				highlighted: false,
				cta: t('pricing.contactSales'),
				planType: 'PREMIUM'
			}
		] as PricingPlan[];
	});

	function getPrice(plan: PricingPlan): number {
		return isAnnual ? plan.annualPrice : plan.monthlyPrice;
	}

	function getSavingsPercentage(plan: PricingPlan): number {
		if (plan.monthlyPrice === 0) return 0;
		const savings = ((plan.monthlyPrice - plan.annualPrice) / plan.monthlyPrice) * 100;
		return Math.round(savings);
	}

	function getOverallSavings(): number {
		const proPlan = plans.find((p) => p.planType === 'PRO_MONTHLY');
		if (!proPlan) return 20;
		return getSavingsPercentage(proPlan);
	}

	async function handleCheckout(plan: PricingPlan) {
		if (!plan.planType || plan.planType === 'FREE') {
			goto('/auth');
			return;
		}

		if (plan.planType === 'PREMIUM') {
			// Contact sales - you can implement this
			alert('Please contact sales for Premium plan');
			return;
		}

		loading = true;
		try {
			const planType = isAnnual ? 'PRO_YEARLY' : (plan.planType as 'PRO_MONTHLY');
			const response = await apiClient.createCheckout(planType);
			if (response?.checkout_url) {
				window.location.href = response.checkout_url;
			} else {
				throw new Error('No checkout URL received');
			}
		} catch (err: any) {
			console.error('Checkout error:', err);
			const errorMessage = err?.message || 'Failed to create checkout session. Please try again.';
			alert(errorMessage);
		} finally {
			loading = false;
		}
	}

	function handleCtaClick(plan: PricingPlan) {
		if (plan.planType === 'FREE') {
			goto('/auth');
		} else if (plan.planType === 'PREMIUM') {
			// Redirect to checkout for Premium
			goto(`/checkout?plan=premium&annual=${isAnnual ? 'true' : 'false'}`);
		} else {
			// Redirect to checkout for Pro
			goto(`/checkout?plan=pro&annual=${isAnnual ? 'true' : 'false'}`);
		}
	}

	const visiblePlans = $derived(
		plans.filter((plan) => !(isAnnual && plan.hideInAnnual))
	);
</script>

<div class="relative min-h-screen overflow-hidden">
	<!-- Animated Background Orbs -->
	<PriceOrbs />

	<!-- Main Content -->
	<div class="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
		<!-- Header -->
		<div class="text-center mb-12 lg:mb-16 animate-fade-in">
			<div class="inline-flex items-center gap-2 mb-4">
				{#if isAnnual}
					<span
						class="px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-cyan-400 to-blue-500 text-white animate-fade-in"
					>
						{t('pricing.saveAnnually', { percentage: getOverallSavings() })}
					</span>
				{/if}
			</div>
			<h1
				class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4 bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent"
			>
				{t('pricing.title')}
			</h1>
			<p class="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto">
				{t('pricing.subtitle')}
			</p>
		</div>

		<!-- Billing Toggle -->
		<div class="flex justify-center mb-12 lg:mb-16 animate-fade-in">
			<BillingToggle bind:isAnnual />
		</div>

		<!-- Pricing Grid -->
		<div
			class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 max-w-7xl mx-auto animate-fade-in"
		>
			{#each visiblePlans as plan, index}
				<PriceCard
					{plan}
					price={getPrice(plan)}
					isAnnual={isAnnual}
					savings={getSavingsPercentage(plan)}
					isCurrentPlan={currentPlan === plan.planType}
					{loading}
					onCtaClick={() => handleCtaClick(plan)}
					style="animation-delay: {index * 100}ms"
				/>
			{/each}
		</div>
	</div>
</div>

<style>
	@keyframes fadeIn {
		from {
			opacity: 0;
			transform: translateY(20px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.animate-fade-in {
		animation: fadeIn 0.6s ease-out forwards;
		opacity: 0;
	}

	@media (prefers-reduced-motion: reduce) {
		.animate-fade-in {
			animation: none;
			opacity: 1;
		}
	}
</style>
