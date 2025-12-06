<script lang="ts">
	import { fade } from 'svelte/transition';
	import { cn } from '$lib/utils';
	import X from 'lucide-svelte/icons/x';
	import AlertCircle from 'lucide-svelte/icons/alert-circle';
	import CheckCircle from 'lucide-svelte/icons/check-circle';
	import Info from 'lucide-svelte/icons/info';
	import AlertTriangle from 'lucide-svelte/icons/alert-triangle';

	type Variant = 'default' | 'success' | 'error' | 'warning' | 'info';

	interface Props {
		open?: boolean;
		title?: string;
		message: string;
		variant?: Variant;
		class?: string;
		onClose?: () => void;
		onConfirm?: () => void;
		showCancel?: boolean;
		confirmText?: string;
		cancelText?: string;
	}

	let {
		open = $bindable(false),
		title,
		message,
		variant = 'default',
		class: className = '',
		onClose,
		onConfirm,
		showCancel = false,
		confirmText = 'OK',
		cancelText = 'Cancel'
	}: Props = $props();

	const variantStyles = {
		default: 'bg-background border-border',
		success: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
		error: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
		warning: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
		info: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
	};

	const textStyles = {
		default: 'text-foreground',
		success: 'text-green-800 dark:text-green-200',
		error: 'text-red-800 dark:text-red-200',
		warning: 'text-yellow-800 dark:text-yellow-200',
		info: 'text-blue-800 dark:text-blue-200'
	};

	const iconMap = {
		default: Info,
		success: CheckCircle,
		error: AlertCircle,
		warning: AlertTriangle,
		info: Info
	};

	const Icon = iconMap[variant];

	function handleClose() {
		open = false;
		onClose?.();
	}
</script>

{#if open}
	<div
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
		onclick={(e) => {
			if (e.target === e.currentTarget) handleClose();
		}}
		onkeydown={(e) => {
			if (e.key === 'Escape') handleClose();
		}}
		role="alertdialog"
		aria-modal="true"
		aria-labelledby={title ? 'alert-title' : undefined}
		aria-describedby="alert-message"
		tabindex="-1"
		transition:fade
	>
		<div
			class={cn(
				'bg-background rounded-lg shadow-xl max-w-md w-full mx-4 border-2',
				variantStyles[variant],
				className
			)}
			transition:fade
		>
			<div class="p-6">
				<div class="flex items-start gap-4">
					<div class={cn('flex-shrink-0', textStyles[variant])}>
						<Icon class="h-6 w-6" />
					</div>
					<div class="flex-1">
						{#if title}
							<h3 id="alert-title" class={cn('text-lg font-semibold mb-2', textStyles[variant])}>{title}</h3>
						{/if}
						<p id="alert-message" class={cn('text-sm', textStyles[variant])}>{message}</p>
					</div>
					<button
						type="button"
						onclick={handleClose}
						class={cn(
							'flex-shrink-0 p-1 rounded-md hover:bg-black/5 dark:hover:bg-white/5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
							textStyles[variant]
						)}
						aria-label="Close alert"
					>
						<X class="h-5 w-5" />
					</button>
				</div>
				<div class="mt-4 flex justify-end gap-2">
					{#if showCancel}
						<button
							type="button"
							onclick={handleClose}
							class="px-4 py-2 rounded-md text-sm font-medium transition-colors bg-muted hover:bg-muted/80 text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
						>
							{cancelText}
						</button>
					{/if}
					<button
						type="button"
						onclick={() => {
							if (onConfirm) {
								onConfirm();
							}
							handleClose();
						}}
						class={cn(
							'px-4 py-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
							variant === 'error'
								? 'bg-red-600 hover:bg-red-700 text-white focus-visible:ring-red-600'
								: variant === 'success'
									? 'bg-green-600 hover:bg-green-700 text-white focus-visible:ring-green-600'
									: variant === 'warning'
										? 'bg-yellow-600 hover:bg-yellow-700 text-white focus-visible:ring-yellow-600'
										: 'bg-primary hover:bg-primary/90 text-primary-foreground focus-visible:ring-primary'
						)}
					>
						{confirmText}
					</button>
				</div>
			</div>
		</div>
	</div>
{/if}

