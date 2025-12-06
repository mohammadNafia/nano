<script lang="ts">
	import '../app.css';
	import { onMount } from 'svelte';
	import { browser } from '$app/environment';
	import type { Snippet } from 'svelte';
	import { authStore, themeStore, langStore } from '$lib/stores';
	import Navbar from '$lib/components/layout/Navbar.svelte';
	import Footer from '$lib/components/layout/Footer.svelte';
	import ScrollProgress from '$lib/components/ScrollProgress.svelte';

	interface Props {
		children?: Snippet;
	}

	let { children }: Props = $props();

	// Initialize theme immediately if in browser to prevent flash
	if (browser) {
		themeStore.init();
	}

	let initialized = $state(false);

	onMount(async () => {
		// Only initialize once - prevent multiple re-initializations
		if (!initialized && browser) {
			// Initialize authStore and wait for it to complete
			// This ensures all pages can rely on authStore being initialized
			await authStore.init();
			langStore.init();
			initialized = true;
		}
	});
</script>

<div class="min-h-screen flex flex-col global-layout">
	<Navbar />
	<ScrollProgress />
	<main class="flex-1 global-main">
		{#if children}
			<div class="global-wrapper">
				<div class="global-container">
					{@render children()}
				</div>
			</div>
		{/if}
	</main>
	<Footer />
</div>

<style>
	:global(.global-layout) {
		/* Ensures smooth scrolling and proper layout */
		scroll-behavior: smooth;
		position: relative;
	}

	:global(.global-main) {
		/* Main content area styling */
		position: relative;
		overflow-x: hidden;
		width: 100%;
	}

	:global(.global-wrapper) {
		/* Global wrapper automatically applied to all pages */
		width: 100%;
		min-height: calc(100vh - 8rem);
		position: relative;
		/* Remove expensive animations - use CSS transitions instead */
		opacity: 1;
		transform: scale(1);
	}

	/* Ensure all direct children of global-wrapper get proper spacing */
	:global(.global-wrapper > .global-container > *) {
		width: 100%;
	}

	/* Allow pages to break out of container for full-width sections */
	:global(.global-wrapper > .global-container > .full-width) {
		width: 100vw;
		max-width: 100vw;
		margin-left: calc(-50vw + 50%);
		margin-right: calc(-50vw + 50%);
		padding-left: var(--container-padding-mobile, 1rem);
		padding-right: var(--container-padding-mobile, 1rem);
	}

	@media (min-width: 640px) {
		:global(.global-wrapper > .global-container > .full-width) {
			padding-left: var(--container-padding-tablet, 1.5rem);
			padding-right: var(--container-padding-tablet, 1.5rem);
		}
	}

	@media (min-width: 1024px) {
		:global(.global-wrapper > .global-container > .full-width) {
			padding-left: var(--container-padding-desktop, 2rem);
			padding-right: var(--container-padding-desktop, 2rem);
		}
	}
</style>
