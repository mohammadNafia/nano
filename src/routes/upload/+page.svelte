<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import { goto } from '$app/navigation';
	import { browser } from '$app/environment';
	import { fade } from 'svelte/transition';
	import { authStore, usageStore, historyStore } from '$lib/stores';
	import Card from '$lib/components/ui/Card.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Modal from '$lib/components/ui/Modal.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import AuthRequiredModal from '$lib/components/AuthRequiredModal.svelte';
	// Optimize icon imports - tree-shakeable per-icon imports
	import Upload from 'lucide-svelte/icons/upload';
	import Trash2 from 'lucide-svelte/icons/trash-2';
	import Edit from 'lucide-svelte/icons/edit';
	import Save from 'lucide-svelte/icons/save';
	import ThumbsUp from 'lucide-svelte/icons/thumbs-up';
	import ThumbsDown from 'lucide-svelte/icons/thumbs-down';
	import Copy from 'lucide-svelte/icons/copy';
	import FileText from 'lucide-svelte/icons/file-text';
	import X from 'lucide-svelte/icons/x';
	import Download from 'lucide-svelte/icons/download';
	import FileDown from 'lucide-svelte/icons/file-down';
	import { cn } from '$lib/utils';
	
	// Lazy load heavy libraries - only load when needed
	let jsPDF: any = null;
	let Document: any = null;
	let Packer: any = null;
	let Paragraph: any = null;
	let TextRun: any = null;
	let fileSaver: any = null;

	// State variables
	let dragOver = $state(false);
	let file: File | null = $state(null);
	let isProcessing = $state(false);
	let isEditing = $state(false);
	let result: string | null = $state(null);
	let displayedText = $state('');
	let typingInterval: ReturnType<typeof setInterval> | null = $state(null);
	let editedText = $state('');
	let filePreviewUrl = $state<string | null>(null);
	
	// Alert state
	let alertOpen = $state(false);
	let alertTitle = $state('');
	let alertMessage = $state('');
	let alertVariant: 'default' | 'success' | 'error' | 'warning' | 'info' = $state('default');
	
	// Export modal state
	let showExportModal = $state(false);
	
	// Auth required modal state
	let showAuthModal = $state(false);

	// Auth state - optimized to prevent unnecessary re-renders
	let authState = $state<{ isAuthenticated: boolean; user: any; isInitialized: boolean }>({ 
		isAuthenticated: false, 
		user: null,
		isInitialized: false
	});
	
	authStore.subscribe((state) => {
		// Only update if values actually changed
		// Only consider authenticated if both authenticated AND initialized
		const effectiveAuthenticated = state.isAuthenticated && state.isInitialized;
		
		if (
			authState.isAuthenticated !== effectiveAuthenticated ||
			authState.user !== state.user ||
			authState.isInitialized !== (state.isInitialized || false)
		) {
			authState = {
				isAuthenticated: effectiveAuthenticated,
				user: state.user,
				isInitialized: state.isInitialized || false
			};
		}
	});

	// Usage state
	let usageState = $state<{
		uploads_this_month: number;
		remaining_uploads: number | typeof Infinity;
		monthly_limit: number | typeof Infinity;
	}>({
		uploads_this_month: 0,
		remaining_uploads: 0,
		monthly_limit: 5
	});

	usageStore.subscribe((state) => {
		usageState = state;
	});

	onMount(() => {
		// Don't re-initialize authStore - it's already initialized in layout
		// Only initialize historyStore if needed
		historyStore.init();
	});

	// Watch for subscription changes and update usage store
	$effect(() => {
		if (authState.isAuthenticated && authState.user?.isSubscribed) {
			usageStore.setUnlimited();
		}
	});

	onDestroy(() => {
		if (typingInterval) {
			clearInterval(typingInterval);
		}
		if (filePreviewUrl) {
			URL.revokeObjectURL(filePreviewUrl);
		}
	});

	// File handling
	function checkAuthAndProceed(callback: () => void) {
		if (!authState.isAuthenticated) {
			showAuthModal = true;
			return;
		}
		callback();
	}

	function handleDragOver(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();
		if (!isProcessing) {
			dragOver = true;
		}
	}

	function handleDragLeave(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();
		dragOver = false;
	}

	function handleDrop(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();
		dragOver = false;

		if (isProcessing) return;

		const files = e.dataTransfer?.files;
		if (files && files.length > 0) {
			checkAuthAndProceed(() => {
				handleFile(files[0]);
			});
		}
	}

	function handleFileSelect(e: Event) {
		const target = e.target as HTMLInputElement;
		const files = target.files;
		if (files && files.length > 0) {
			checkAuthAndProceed(() => {
				handleFile(files[0]);
			});
		}
	}

	function showAlert(title: string, message: string, variant: 'default' | 'success' | 'error' | 'warning' | 'info' = 'default') {
		alertTitle = title;
		alertMessage = message;
		alertVariant = variant;
		alertOpen = true;
	}

	function handleFile(selectedFile: File) {
		// Validate file type
		const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'application/pdf'];
		if (!validTypes.includes(selectedFile.type)) {
			showAlert('Invalid File Type', 'Please upload JPG, PNG, SVG, or PDF files.', 'error');
			return;
		}

		// Validate file size (max 10MB)
		if (selectedFile.size > 10 * 1024 * 1024) {
			showAlert('File Too Large', 'File size exceeds 10MB limit.', 'error');
			return;
		}

		// Check usage limit
		if (
			usageState.remaining_uploads !== Infinity &&
			usageState.remaining_uploads <= 0
		) {
			showAlert('Upload Limit Reached', 'Please upgrade your plan to continue uploading.', 'warning');
			return;
		}

		// Clear previous state
		if (filePreviewUrl) {
			URL.revokeObjectURL(filePreviewUrl);
		}
		result = null;
		displayedText = '';
		editedText = '';
		isEditing = false;

		file = selectedFile;

		// Create preview URL for images
		if (selectedFile.type.startsWith('image/')) {
			filePreviewUrl = URL.createObjectURL(selectedFile);
		} else {
			filePreviewUrl = null;
		}

		// Start processing
		processFile();
	}

	async function processFile() {
		if (!file) return;

		isProcessing = true;
		result = null;
		displayedText = '';

		// Mock OCR processing with 2.5 second delay
		await new Promise((resolve) => setTimeout(resolve, 2500));

		// Generate mock OCR text
		const mockText = `Invoice #1023  

Date: Dec 05, 2025  

Items:

1. Web Design Services - $1,200.00

2. Hosting (Annual) - $240.00

Total Due: $1,440.00`;

		result = mockText;
		editedText = mockText;
		isProcessing = false;

		// Save to history store
		historyStore.add({
			text: mockText,
			file_name: file.name,
			file_type: file.type,
			confidence_score: 0.95
		});

		// Increment usage
		usageStore.incrementUsage();
		
		// Invalidate dashboard cache since we added a new upload
		if (browser) {
			const { apiClient } = await import('$lib/api');
			apiClient.invalidateDashboardCache();
		}

		// Start typing animation
		startTypingAnimation(mockText);
	}

	function startTypingAnimation(text: string) {
		displayedText = '';
		let index = 0;

		if (typingInterval) {
			clearInterval(typingInterval);
		}

		typingInterval = setInterval(() => {
			if (index < text.length) {
				displayedText += text[index];
				index++;
			} else {
				if (typingInterval) {
					clearInterval(typingInterval);
					typingInterval = null;
				}
			}
		}, 30);
	}

	function clearFile() {
		if (filePreviewUrl) {
			URL.revokeObjectURL(filePreviewUrl);
			filePreviewUrl = null;
		}
		file = null;
		result = null;
		displayedText = '';
		editedText = '';
		isEditing = false;
		if (typingInterval) {
			clearInterval(typingInterval);
			typingInterval = null;
		}
	}

	function toggleEdit() {
		if (isEditing) {
			// Save
			if (result) {
				result = editedText;
				displayedText = editedText;
			}
		}
		isEditing = !isEditing;
	}

	function copyText() {
		const textToCopy = isEditing ? editedText : result || '';
		if (textToCopy) {
			navigator.clipboard.writeText(textToCopy);
			showAlert('Copied!', 'Text has been copied to clipboard.', 'success');
		}
	}

	function showExportOptions() {
		const textToExport = isEditing ? editedText : result || '';
		if (!textToExport) {
			showAlert('No Text', 'There is no text to export.', 'warning');
			return;
		}
		showExportModal = true;
	}

	async function exportToPDF() {
		const textToExport = isEditing ? editedText : result || '';
		if (!textToExport) return;

		try {
			// Lazy load jsPDF only when needed
			if (!jsPDF) {
				const jsPDFModule = await import('jspdf');
				jsPDF = jsPDFModule.default;
			}
			
			const doc = new jsPDF();
			const lines = doc.splitTextToSize(textToExport, 180);
			doc.text(lines, 10, 10);
			doc.save(`extracted-text-${Date.now()}.pdf`);
			showExportModal = false;
			showAlert('Exported!', 'Text has been exported as PDF.', 'success');
		} catch (error) {
			showAlert('Export Failed', 'Failed to export PDF. Please try again.', 'error');
		}
	}

	async function exportToWord() {
		const textToExport = isEditing ? editedText : result || '';
		if (!textToExport) return;

		try {
			// Lazy load docx and file-saver only when needed
			if (!Document || !Packer || !Paragraph || !TextRun || !fileSaver) {
				const docxModule = await import('docx');
				Document = docxModule.Document;
				Packer = docxModule.Packer;
				Paragraph = docxModule.Paragraph;
				TextRun = docxModule.TextRun;
				
				const fileSaverModule = await import('file-saver');
				fileSaver = fileSaverModule.default;
			}

			// Split text into paragraphs
			const paragraphs = textToExport.split('\n\n').map(
				(para) =>
					new Paragraph({
						children: para.split('\n').map(
							(line) =>
								new TextRun({
									text: line,
									break: 1
								})
						)
					})
			);

			const doc = new Document({
				sections: [
					{
						properties: {},
						children: paragraphs
					}
				]
			});

			const blob = await Packer.toBlob(doc);
			fileSaver.saveAs(blob, `extracted-text-${Date.now()}.docx`);
			showExportModal = false;
			showAlert('Exported!', 'Text has been exported as Word document.', 'success');
		} catch (error) {
			showAlert('Export Failed', 'Failed to export Word document. Please try again.', 'error');
		}
	}

	function deleteResult() {
		alertTitle = 'Delete Result?';
		alertMessage = 'Are you sure you want to delete this result? This action cannot be undone.';
		alertVariant = 'warning';
		alertOpen = true;
	}

	function handleDeleteConfirm() {
		clearFile();
		alertOpen = false;
	}

	function processAnother() {
		clearFile();
	}

	function isImageFile(file: File | null): boolean {
		if (!file) return false;
		return file.type.startsWith('image/');
	}

	function isPDFFile(file: File | null): boolean {
		if (!file) return false;
		return file.type === 'application/pdf';
	}
</script>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
	<!-- Usage Counter -->
	<div class="mb-6">
		<div class="flex items-center justify-between">
			<h1 class="text-3xl font-bold">Upload & Extract Text</h1>
			{#if authState.isAuthenticated}
				<div class="text-sm text-muted-foreground">
					<span class="font-semibold">{usageState.uploads_this_month}</span>
					<span class="mx-1">/</span>
					<span>
						{usageState.monthly_limit === Infinity
							? '∞'
							: usageState.monthly_limit}
					</span>
					<span class="ml-1">uploads used</span>
				</div>
			{/if}
		</div>
	</div>

		<!-- Two Column Layout -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
			<!-- Left Column: Upload Area -->
			<Card class="p-6">
				<h2 class="text-xl font-semibold mb-4" id="upload-section-title">Upload File</h2>

				<!-- Upload Area -->
				<div
					role="button"
					tabindex="0"
					aria-label="Upload area: Drag and drop files here or click to browse"
					class={cn(
						'relative border-2 border-dashed rounded-lg p-8 text-center transition-all cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
						dragOver
							? 'border-primary bg-primary/5'
							: 'border-muted-foreground/25 hover:border-primary/50',
						isProcessing && 'opacity-50 pointer-events-none'
					)}
					ondragover={handleDragOver}
					ondragleave={handleDragLeave}
					ondrop={handleDrop}
					onclick={() => {
						if (!file && !isProcessing) {
							checkAuthAndProceed(() => {
								const input = document.getElementById('file-input') as HTMLInputElement;
								input?.click();
							});
						}
					}}
					onkeydown={(e) => {
						if ((e.key === 'Enter' || e.key === ' ') && !file && !isProcessing) {
							e.preventDefault();
							checkAuthAndProceed(() => {
								const input = document.getElementById('file-input') as HTMLInputElement;
								input?.click();
							});
						}
					}}
				>
					{#if !file}
						<!-- Empty State -->
						<div class="flex flex-col items-center gap-4">
							<Upload class="h-12 w-12 text-muted-foreground" />
							<div>
								<p class="text-lg font-medium mb-2">Drag & Drop your file here</p>
								<p class="text-sm text-muted-foreground mb-4">
									or click to browse
								</p>
								<Button
									variant="outline"
									onclick={(e) => {
										e.stopPropagation();
										checkAuthAndProceed(() => {
											const input = document.getElementById('file-input') as HTMLInputElement;
											input?.click();
										});
									}}
									disabled={isProcessing}
								>
									<Upload class="h-4 w-4 mr-2" />
									Select File
								</Button>
								<p class="text-xs text-muted-foreground mt-4">
									Supported: JPG, PNG, SVG, PDF (Max 10MB)
								</p>
							</div>
						</div>
					{:else}
						<!-- File Preview -->
						<div class="relative group">
							{#if isImageFile(file)}
								<img
									src={filePreviewUrl || ''}
									alt={`Preview of uploaded file: ${file.name}`}
									class="max-h-64 mx-auto rounded-lg object-contain"
								/>
							{:else if isPDFFile(file)}
								<div class="flex flex-col items-center gap-3 py-8">
									<FileText class="h-16 w-16 text-muted-foreground" />
									<p class="text-sm font-medium">{file.name}</p>
								</div>
							{/if}

							<!-- Clear Button -->
							<button
								type="button"
								class="absolute top-2 right-2 p-2 rounded-full bg-background/80 hover:bg-background border shadow-sm transition-colors z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={(e) => {
									e.stopPropagation();
									clearFile();
								}}
								disabled={isProcessing}
								aria-label="Clear file"
							>
								<Trash2 class="h-4 w-4 text-destructive" />
							</button>

							<!-- Process Another Button Overlay -->
							<div
								class="absolute inset-0 flex items-center justify-center bg-background/80 rounded-lg opacity-0 hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto"
							>
								<Button
									variant="default"
									onclick={(e) => {
										e.stopPropagation();
										processAnother();
									}}
									class="pointer-events-auto"
								>
									Process Another File
								</Button>
							</div>
						</div>
					{/if}

					<!-- Processing Overlay -->
					{#if isProcessing}
						<div
							class="absolute inset-0 bg-background/80 backdrop-blur-sm rounded-lg flex flex-col items-center justify-center gap-4"
							transition:fade
						>
							<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
							<p class="text-lg font-medium">Extracting text...</p>
						</div>
					{/if}

					<input
						id="file-input"
						type="file"
						accept="image/jpeg,image/jpg,image/png,image/svg+xml,application/pdf"
						onchange={handleFileSelect}
						class="hidden"
						disabled={isProcessing}
					/>
				</div>
			</Card>

			<!-- Right Column: Result Area -->
			<Card class="p-6">
				<div class="flex items-center justify-between mb-4">
					<h2 class="text-xl font-semibold" id="extracted-text-title">Extracted Text</h2>
					{#if result}
						<div class="flex gap-2">
							<!-- Action Buttons -->
							<button
								type="button"
								class="p-2 rounded-md hover:bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={toggleEdit}
								title={isEditing ? 'Save' : 'Edit'}
								aria-label={isEditing ? 'Save changes' : 'Edit text'}
							>
								{#if isEditing}
									<Save class="h-4 w-4" />
								{:else}
									<Edit class="h-4 w-4" />
								{/if}
							</button>
							<button
								type="button"
								class="p-2 rounded-md hover:bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={() => {}}
								title="Thumbs Up"
								aria-label="Thumbs up"
							>
								<ThumbsUp class="h-4 w-4" />
							</button>
							<button
								type="button"
								class="p-2 rounded-md hover:bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={() => {}}
								title="Thumbs Down"
								aria-label="Thumbs down"
							>
								<ThumbsDown class="h-4 w-4" />
							</button>
							<button
								type="button"
								class="p-2 rounded-md hover:bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={copyText}
								title="Copy"
								aria-label="Copy text to clipboard"
							>
								<Copy class="h-4 w-4" />
							</button>
							<button
								type="button"
								class="p-2 rounded-md hover:bg-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
								onclick={showExportOptions}
								title="Export"
								aria-label="Export text"
							>
								<FileDown class="h-4 w-4" />
							</button>
							<button
								type="button"
								class="p-2 rounded-md hover:bg-destructive/10 hover:text-destructive transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-destructive"
								onclick={deleteResult}
								title="Delete"
								aria-label="Delete result"
							>
								<X class="h-4 w-4" />
							</button>
						</div>
					{/if}
				</div>

				<!-- Result Content -->
				<div class="min-h-[400px]">
					{#if !file}
						<!-- Placeholder -->
						<div class="flex items-center justify-center h-full text-muted-foreground">
							<p>Upload a file to extract text</p>
						</div>
					{:else if isProcessing}
						<!-- Loading Skeleton -->
						<div class="space-y-3">
							{#each Array(5) as _}
								<div class="h-4 bg-muted rounded animate-pulse"></div>
							{/each}
						</div>
					{:else if result}
						<!-- Result Display -->
						{#if isEditing}
							<textarea
								bind:value={editedText}
								class="w-full h-full min-h-[400px] p-4 border rounded-lg resize-none bg-background text-foreground border-input focus:outline-none focus:ring-2 focus:ring-primary placeholder:text-muted-foreground"
								placeholder="Edit extracted text..."
							></textarea>
						{:else}
							<div
								class="p-4 border rounded-lg bg-muted/30 min-h-[400px] whitespace-pre-wrap font-mono text-sm"
							>
								{displayedText}
							</div>
						{/if}
					{/if}
				</div>
			</Card>
		</div>

	<!-- Auth Required Modal -->
	<AuthRequiredModal
		bind:show={showAuthModal}
		onSignIn={() => goto('/auth')}
	/>

	<!-- Alert Modal -->
	<Alert
		bind:open={alertOpen}
		title={alertTitle}
		message={alertMessage}
		variant={alertVariant}
		onClose={() => (alertOpen = false)}
		onConfirm={alertTitle === 'Delete Result?' ? handleDeleteConfirm : undefined}
		showCancel={alertTitle === 'Delete Result?'}
		confirmText={alertTitle === 'Delete Result?' ? 'Delete' : 'OK'}
	/>

	<!-- Export Options Modal -->
	<Modal bind:open={showExportModal} title="Export Text">
		<div class="space-y-4">
			<p class="text-sm text-muted-foreground">
				Choose a format to export the extracted text:
			</p>
			<div class="grid grid-cols-2 gap-4">
				<button
					type="button"
					onclick={exportToPDF}
					class="flex flex-col items-center gap-3 p-6 border-2 border-dashed rounded-lg hover:border-primary hover:bg-primary/5 transition-colors group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
					aria-label="Export as PDF"
				>
					<FileText class="h-12 w-12 text-primary group-hover:scale-110 transition-transform" />
					<div class="text-center">
						<p class="font-semibold">PDF Document</p>
						<p class="text-xs text-muted-foreground mt-1">Portable Document Format</p>
					</div>
				</button>
				<button
					type="button"
					onclick={exportToWord}
					class="flex flex-col items-center gap-3 p-6 border-2 border-dashed rounded-lg hover:border-primary hover:bg-primary/5 transition-colors group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
					aria-label="Export as Word document"
				>
					<FileDown class="h-12 w-12 text-primary group-hover:scale-110 transition-transform" />
					<div class="text-center">
						<p class="font-semibold">Word Document</p>
						<p class="text-xs text-muted-foreground mt-1">Microsoft Word (.docx)</p>
					</div>
				</button>
			</div>
			<div class="flex justify-end gap-2 pt-4 border-t">
				<Button variant="outline" onclick={() => (showExportModal = false)}>
					Cancel
				</Button>
			</div>
		</div>
	</Modal>
</div>
