<script lang="ts">
	import { onMount } from 'svelte';
	import { t } from '$lib/i18n';
	import Card from './ui/Card.svelte';
	import Button from './ui/Button.svelte';

	interface Props {
		text: string;
		confidence?: number;
		onClose?: () => void;
		onSave?: (editedText: string) => void;
		class?: string;
	}

	let {
		text: initialText,
		confidence,
		onClose,
		onSave,
		class: className = ''
	}: Props = $props();

	let displayedText = $state('');
	let editedText = $state('');
	let isTyping = $state(true);
	let skipAnimation = $state(false);
	let isEditing = $state(false);

	onMount(() => {
		if (skipAnimation) {
			displayedText = initialText;
			editedText = initialText;
			isTyping = false;
			return;
		}

		let index = 0;
		const typingSpeed = 20; // milliseconds per character

		const interval = setInterval(() => {
			if (index < initialText.length) {
				displayedText = initialText.slice(0, index + 1);
				index++;
			} else {
				clearInterval(interval);
				isTyping = false;
				editedText = initialText;
			}
		}, typingSpeed);

		return () => clearInterval(interval);
	});

	function handleSkip() {
		skipAnimation = true;
		displayedText = initialText;
		editedText = initialText;
		isTyping = false;
	}

	function startEditing() {
		isEditing = true;
		editedText = displayedText || initialText;
	}

	function saveEdit() {
		onSave?.(editedText);
		isEditing = false;
	}

	function cancelEdit() {
		editedText = displayedText || initialText;
		isEditing = false;
	}

	function copyToClipboard() {
		const textToCopy = isEditing ? editedText : (displayedText || initialText);
		navigator.clipboard.writeText(textToCopy);
		// You could add a toast notification here
	}
</script>

<Card class={className}>
	<div class="p-6">
		<div class="flex items-center justify-between mb-4">
			<h2 class="text-lg font-semibold">{t('ocrResult.title')}</h2>
			<div class="flex items-center gap-2">
				{#if confidence !== undefined}
					<span class="text-sm text-muted-foreground">
						{t('ocrResult.confidence', { percentage: Math.round(confidence * 100) })}
					</span>
				{/if}
				{#if isTyping}
					<Button variant="ghost" size="sm" onclick={handleSkip} aria-label="Skip typing animation">{t('ocrResult.skip')}</Button>
				{/if}
				<Button variant="ghost" size="sm" onclick={copyToClipboard} aria-label="Copy text to clipboard">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						class="h-4 w-4"
						viewBox="0 0 20 20"
						fill="currentColor"
						aria-hidden="true"
					>
						<path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
						<path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
					</svg>
				</Button>
				{#if onClose}
					<Button variant="ghost" size="sm" onclick={onClose} aria-label="Close">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-4 w-4"
							viewBox="0 0 20 20"
							fill="currentColor"
							aria-hidden="true"
						>
							<path
								fill-rule="evenodd"
								d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
								clip-rule="evenodd"
							/>
						</svg>
					</Button>
				{/if}
			</div>
		</div>
		<div class="bg-muted rounded-md p-4 min-h-[200px] max-h-[500px] overflow-y-auto">
			{#if isEditing}
				<textarea
					bind:value={editedText}
					class="w-full bg-transparent text-sm font-mono resize-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded"
					rows="10"
					aria-label="Edit extracted text"
				></textarea>
			{:else}
				<p class="whitespace-pre-wrap text-sm font-mono">{displayedText || initialText}</p>
				{#if isTyping}
					<span class="inline-block w-2 h-4 bg-primary animate-pulse ml-1">|</span>
				{/if}
			{/if}
		</div>
		<div class="flex items-center justify-between mt-4">
			<div class="flex items-center gap-2">
				{#if isEditing}
					<Button variant="default" size="sm" onclick={saveEdit}>{t('ocrResult.save')}</Button>
					<Button variant="outline" size="sm" onclick={cancelEdit}>{t('ocrResult.cancel')}</Button>
				{:else}
					<Button variant="outline" size="sm" onclick={startEditing}>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							class="h-4 w-4 mr-1 rtl:mr-0 rtl:ml-1"
							viewBox="0 0 20 20"
							fill="currentColor"
						>
							<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
						</svg>
						{t('ocrResult.edit')}
					</Button>
				{/if}
			</div>
		</div>
	</div>
</Card>

