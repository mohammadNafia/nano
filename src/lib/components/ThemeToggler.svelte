<script lang="ts">
	import { themeStore } from '$lib/stores/themeStore';
	import { onMount } from 'svelte';
	import { cubicOut } from 'svelte/easing';
	import { scale } from 'svelte/transition';

	let currentTheme = $state<'light' | 'dark'>('light');
	let isAnimating = $state(false);
	let rotation = $state(0);

	onMount(() => {
		const unsubscribe = themeStore.subscribe((theme) => {
			currentTheme = theme;
		});
		return unsubscribe;
	});

	function handleToggle() {
		if (isAnimating) return;
		
		isAnimating = true;
		rotation += 180;
		themeStore.toggleTheme();
		
		setTimeout(() => {
			isAnimating = false;
		}, 500);
	}
</script>

<button
	aria-label="Toggle theme"
	onclick={handleToggle}
	class="relative w-10 h-10 rounded-full flex items-center justify-center overflow-hidden transition-colors duration-300 ease-[cubic-bezier(0.4,0.0,0.2,1)] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
	style="transform: rotate({rotation}deg); transition: transform 500ms cubic-bezier(0.4, 0.0, 0.2, 1);"
>
	<!-- Background gradient overlay -->
	<div
		class="absolute inset-0 rounded-full transition-opacity duration-500 ease-[cubic-bezier(0.4,0.0,0.2,1)]"
		class:opacity-0={currentTheme === 'light'}
		class:opacity-100={currentTheme === 'dark'}
		style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);"
	></div>

	<!-- Icon container with counter-rotation -->
	<div
		class="relative w-5 h-5 flex items-center justify-center"
		style="transform: rotate({-rotation}deg); transition: transform 500ms cubic-bezier(0.4, 0.0, 0.2, 1);"
	>
		<!-- Sun Icon -->
		{#if currentTheme === 'light'}
			<div
				in:scale={{ duration: 300, easing: cubicOut, start: 0.8 }}
				out:scale={{ duration: 200, easing: cubicOut, start: 0.8 }}
				class="absolute inset-0 flex items-center justify-center"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="w-5 h-5 text-amber-500"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				>
					<!-- Sun center circle -->
					<circle cx="12" cy="12" r="4" fill="currentColor" />
					<!-- Sun rays -->
					<line x1="12" y1="1" x2="12" y2="3" />
					<line x1="12" y1="21" x2="12" y2="23" />
					<line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
					<line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
					<line x1="1" y1="12" x2="3" y2="12" />
					<line x1="21" y1="12" x2="23" y2="12" />
					<line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
					<line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
				</svg>
			</div>
		{/if}

		<!-- Moon Icon -->
		{#if currentTheme === 'dark'}
			<div
				in:scale={{ duration: 300, easing: cubicOut, start: 0.8 }}
				out:scale={{ duration: 200, easing: cubicOut, start: 0.8 }}
				class="absolute inset-0 flex items-center justify-center"
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="w-5 h-5 text-slate-200 dark:text-slate-400"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				>
					<path
						d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
						fill="currentColor"
					/>
				</svg>
			</div>
		{/if}
	</div>
</button>

