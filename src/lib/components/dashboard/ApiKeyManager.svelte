<script lang="ts">
	import { onMount } from 'svelte';
	import { t } from '$lib/i18n';
	import { apiClient } from '$lib/api';
	import Card from '../ui/Card.svelte';
	import Button from '../ui/Button.svelte';
	import Modal from '../ui/Modal.svelte';

	interface ApiKey {
		id: number;
		label?: string;
		key_hash: string;
		last_used_at?: string;
		created_at: string;
	}

	let apiKeys = $state<ApiKey[]>([]);
	let loading = $state(false);
	let showCreateModal = $state(false);
	let newKeyLabel = $state('');
	let createdKey = $state<string | null>(null);
	let showKeyModal = $state(false);

	onMount(() => {
		loadApiKeys();
	});

	async function loadApiKeys() {
		loading = true;
		try {
			const response = await apiClient.getApiKeys();
			apiKeys = response.data || response || [];
		} catch (err) {
			console.error('Failed to load API keys:', err);
		} finally {
			loading = false;
		}
	}

	async function createApiKey() {
		loading = true;
		try {
			const response = await apiClient.createApiKey(newKeyLabel || undefined);
			createdKey = response.key;
			newKeyLabel = '';
			showCreateModal = false;
			showKeyModal = true;
			await loadApiKeys();
		} catch (err) {
			console.error('Failed to create API key:', err);
			alert(t('apiKeys.createError'));
		} finally {
			loading = false;
		}
	}

	async function deleteApiKey(id: number) {
		if (!confirm(t('apiKeys.revokeConfirm'))) {
			return;
		}

		loading = true;
		try {
			await apiClient.deleteApiKey(id);
			await loadApiKeys();
		} catch (err) {
			console.error('Failed to delete API key:', err);
			alert(t('apiKeys.revokeError'));
		} finally {
			loading = false;
		}
	}

	function maskKey(key: string) {
		if (!key) return '••••••••';
		return key.slice(0, 8) + '••••••••' + key.slice(-4);
	}
</script>

<Card class="p-6">
	<div class="flex items-center justify-between mb-6">
		<div>
			<h2 class="text-lg font-semibold mb-1">{t('apiKeys.title')}</h2>
			<p class="text-sm text-muted-foreground">
				{t('apiKeys.description')}
			</p>
		</div>
		<Button variant="default" onclick={() => showCreateModal = true}>{t('apiKeys.create')}</Button>
	</div>

	{#if loading && apiKeys.length === 0}
		<div class="text-center py-8">
			<div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
		</div>
	{:else if apiKeys.length === 0}
		<div class="text-center py-8 text-muted-foreground">
			<p>{t('apiKeys.noKeys')}</p>
		</div>
	{:else}
		<div class="space-y-3">
			{#each apiKeys as key (key.id)}
				<div class="flex items-center justify-between p-4 border rounded-lg">
					<div class="flex-1">
						<div class="flex items-center gap-2 mb-1">
							<span class="font-mono text-sm">{maskKey(key.key_hash)}</span>
							{#if key.label}
								<span class="text-xs text-muted-foreground">({key.label})</span>
							{/if}
						</div>
						<div class="text-xs text-muted-foreground">
							{t('apiKeys.created')} {new Date(key.created_at).toLocaleDateString()}
							{#if key.last_used_at}
								• {t('apiKeys.lastUsed')} {new Date(key.last_used_at).toLocaleDateString()}
							{/if}
						</div>
					</div>
					<Button
						variant="destructive"
						size="sm"
						onclick={() => deleteApiKey(key.id)}
						disabled={loading}
					>
						{t('apiKeys.revoke')}
					</Button>
				</div>
			{/each}
		</div>
	{/if}

	<!-- Create API Key Modal -->
	<Modal bind:open={showCreateModal} title={t('apiKeys.createModalTitle')}>
		<div class="space-y-4">
			<div>
				<label for="key-label" class="block text-sm font-medium mb-2">{t('apiKeys.label')}</label>
				<input
					id="key-label"
					type="text"
					bind:value={newKeyLabel}
					placeholder={t('apiKeys.labelPlaceholder')}
					class="w-full px-3 py-2 border rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
					aria-describedby="key-label-description"
				/>
				<p id="key-label-description" class="text-xs text-muted-foreground mt-1">
					{t('apiKeys.labelDescription') || 'Optional label to identify this API key'}
				</p>
			</div>
			<div class="flex justify-end gap-2">
				<Button variant="outline" onclick={() => {
					showCreateModal = false;
					newKeyLabel = '';
				}}>
					{t('apiKeys.cancel')}
				</Button>
				<Button variant="default" onclick={createApiKey} disabled={loading}>
					{loading ? t('apiKeys.creating') : t('apiKeys.createButton')}
				</Button>
			</div>
		</div>
	</Modal>

	<!-- Show Created Key Modal -->
	<Modal bind:open={showKeyModal} title={t('apiKeys.createdModalTitle')}>
		<div class="space-y-4">
			<p class="text-sm text-muted-foreground">
				{t('apiKeys.createdDesc')}
			</p>
			<div class="bg-muted p-4 rounded-md">
				<code class="text-sm font-mono break-all">{createdKey}</code>
			</div>
			<div class="flex justify-end">
				<Button
					variant="default"
					onclick={() => {
						if (createdKey) {
							navigator.clipboard.writeText(createdKey);
						}
						showKeyModal = false;
						createdKey = null;
					}}
				>
					{t('apiKeys.copyAndClose')}
				</Button>
			</div>
		</div>
	</Modal>
</Card>

