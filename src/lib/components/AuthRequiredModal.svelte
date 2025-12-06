<script lang="ts">
	import { fade, scale } from 'svelte/transition';
	import { cubicOut } from 'svelte/easing';
	import Button from './ui/Button.svelte';
	import Lock from 'lucide-svelte/icons/lock';

	interface Props {
		show?: boolean;
		onClose?: () => void;
		onSignIn?: () => void;
	}

	let { show = $bindable(false), onClose, onSignIn }: Props = $props();

	function handleClose() {
		show = false;
		onClose?.();
	}

	function handleSignIn() {
		show = false;
		onSignIn?.();
	}

	function handleBackdropClick(e: MouseEvent) {
		if (e.target === e.currentTarget) {
			handleClose();
		}
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Escape') {
			handleClose();
		}
	}
</script>

{#if show}
	<!-- Backdrop -->
	<div
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
		onclick={handleBackdropClick}
		onkeydown={handleKeydown}
		role="dialog"
		aria-modal="true"
		aria-labelledby="auth-modal-title"
		aria-describedby="auth-modal-description"
		tabindex="-1"
		transition:fade={{ duration: 200, easing: cubicOut }}
	>
		<!-- Modal Content -->
		<div
			class="bg-background rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8 border border-border/50"
			transition:scale={{ duration: 300, easing: cubicOut, start: 0.9 }}
		>
			<!-- Icon -->
			<div class="flex justify-center mb-6">
				<div class="rounded-full bg-primary/10 p-4">
					<Lock class="h-8 w-8 text-primary" />
				</div>
			</div>

			<!-- Title -->
			<h2 id="auth-modal-title" class="text-2xl font-bold text-center mb-3">
				Please sign in first
			</h2>

			<!-- Description -->
			<p id="auth-modal-description" class="text-muted-foreground text-center mb-8 leading-relaxed">
				Please sign in first to use OCR uploads. Create an account or sign in to start extracting text from your documents.
			</p>

			<!-- Actions -->
			<div class="flex flex-col gap-3">
				<Button
					variant="subscribe"
					size="lg"
					onclick={handleSignIn}
					class="w-full"
				>
					Sign In to Continue
				</Button>
				<Button
					variant="ghost"
					size="lg"
					onclick={handleClose}
					class="w-full"
				>
					Cancel
				</Button>
			</div>
		</div>
	</div>
{/if}

