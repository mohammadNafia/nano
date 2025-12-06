<script lang="ts">
	import { t } from '$lib/i18n';
	import Card from '../ui/Card.svelte';

	interface OcrJob {
		id: number;
		created_at: string;
		text_preview: string;
		confidence_score?: number;
	}

	interface Props {
		jobs: OcrJob[];
		class?: string;
	}

	let { jobs, class: className = '' }: Props = $props();

	function formatDate(dateString: string) {
		const date = new Date(dateString);
		return date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit'
		});
	}

	function truncateText(text: string, maxLength: number = 100) {
		if (text.length <= maxLength) return text;
		return text.slice(0, maxLength) + '...';
	}
</script>

<Card class={className}>
	<div class="p-6">
		<h2 class="text-lg font-semibold mb-4">{t('history.title')}</h2>
		<div class="overflow-x-auto">
			<table class="w-full">
				<thead>
					<tr class="border-b">
						<th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-muted-foreground">{t('history.date')}</th>
						<th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-muted-foreground">{t('history.preview')}</th>
						<th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-muted-foreground">{t('history.confidence')}</th>
					</tr>
				</thead>
				<tbody>
					{#each jobs as job (job.id)}
						<tr class="border-b hover:bg-muted/50 transition-colors">
							<td class="py-3 px-4 text-sm">{formatDate(job.created_at)}</td>
							<td class="py-3 px-4 text-sm font-mono text-muted-foreground">
								{truncateText(job.text_preview)}
							</td>
							<td class="py-3 px-4 text-sm">
								{#if job.confidence_score !== undefined}
									<span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-primary/10 text-primary">
										{Math.round(job.confidence_score * 100)}%
									</span>
								{:else}
									<span class="text-muted-foreground">—</span>
								{/if}
							</td>
						</tr>
					{:else}
						<tr>
							<td colspan="3" class="py-8 text-center text-muted-foreground">{t('history.noUploads')}</td>
						</tr>
					{/each}
				</tbody>
			</table>
		</div>
	</div>
</Card>

