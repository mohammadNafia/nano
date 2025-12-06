<script lang="ts">
	import { t } from '$lib/i18n';
	import Button from '$lib/components/ui/Button.svelte';
	import type { Snippet } from 'svelte';

	interface PricingPlan {
		name: string;
		description: string;
		monthlyPrice: number;
		annualPrice: number;
		features: string[];
		highlighted: boolean;
		cta: string;
		planType?: string;
	}

	interface Props {
		plan: PricingPlan;
		price: number;
		isAnnual: boolean;
		savings: number;
		isCurrentPlan?: boolean;
		loading?: boolean;
		onCtaClick: () => void;
		style?: string;
	}

	let {
		plan,
		price,
		isAnnual,
		savings,
		isCurrentPlan = false,
		loading = false,
		onCtaClick,
		style = ''
	}: Props = $props();
</script>

<div
	class="group relative h-full animate-fade-in"
	style={style}
>
	<!-- Glassmorphism Card -->
	<div
		class="relative h-full p-8 rounded-2xl transition-all duration-300 transform hover:scale-105 backdrop-blur-xl border shadow-lg bg-white/60 dark:bg-[#393E46]/80 border-white/20 dark:border-white/10 border-cyan-200/30 dark:border-cyan-500/20 shadow-cyan-500/10 dark:shadow-cyan-500/20 hover:shadow-2xl hover:shadow-cyan-500/20 dark:hover:shadow-cyan-500/30 hover:border-cyan-400/50 dark:hover:border-cyan-400/40"
		class:ring-2={plan.highlighted}
		class:ring-cyan-400={plan.highlighted}
		class:ring-opacity-50={plan.highlighted}
	>
		<!-- Highlighted Badge -->
		{#if plan.highlighted}
			<div
				class="absolute -top-4 left-1/2 -translate-x-1/2 rtl:translate-x-1/2 px-4 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-cyan-400 to-blue-500 text-white shadow-lg"
			>
				{t('pricing.mostPopular')}
			</div>
		{/if}

		<!-- Savings Badge (Annual) -->
		{#if isAnnual && savings > 0}
			<div
				class="absolute -top-3 -right-3 rtl:right-auto rtl:left-3 px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white shadow-md animate-pulse"
			>
				{t('pricing.save', { percentage: savings })}
			</div>
		{/if}

		<!-- Content -->
		<div class="space-y-6">
			<!-- Header -->
			<div>
				<h2 class="text-2xl font-bold mb-2 text-foreground">{plan.name}</h2>
				<p class="text-sm text-muted-foreground leading-relaxed">{plan.description}</p>
			</div>

			<!-- Price -->
			<div class="flex items-baseline gap-2">
				<span class="text-5xl font-bold text-foreground">${price.toFixed(2)}</span>
				<span class="text-muted-foreground">
					/{isAnnual ? t('pricing.perYear') : t('pricing.perMonth')}
				</span>
			</div>

			<!-- Features List -->
			<ul class="space-y-3">
				{#each plan.features as feature}
					<li class="flex items-start gap-3">
						<svg
							class="h-5 w-5 text-cyan-500 dark:text-cyan-400 mt-0.5 flex-shrink-0"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
							aria-hidden="true"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M5 13l4 4L19 7"
							/>
						</svg>
						<span class="text-sm text-muted-foreground">{feature}</span>
					</li>
				{/each}
			</ul>

			<!-- CTA Button -->
			<div class="pt-4">
				{#if isCurrentPlan}
					<Button variant="outline" class="w-full" disabled>
						{t('pricing.currentPlan')}
					</Button>
				{:else}
					<Button
						variant={plan.highlighted ? 'subscribe' : 'default'}
						class="w-full"
						onclick={onCtaClick}
						disabled={loading}
					>
						{loading ? t('common.loading') : plan.cta}
					</Button>
				{/if}
			</div>
		</div>

		<!-- Gradient Border Effect on Hover -->
		<div
			class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
			style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1)); border: 1px solid transparent; background-clip: padding-box;"
		></div>
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
	}

	@media (prefers-reduced-motion: reduce) {
		.animate-fade-in {
			animation: none;
			opacity: 1;
		}
	}
</style>

