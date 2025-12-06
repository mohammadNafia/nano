<script lang="ts">
	import { t } from '$lib/i18n';
</script>

<footer class="border-t bg-background">
	<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			<div class="col-span-1 md:col-span-2">
				<h2 class="text-lg font-bold mb-4 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
					{t('navbar.brand')}
				</h2>
				<p class="text-sm text-muted-foreground max-w-md">
					{t('footer.description')}
				</p>
			</div>
			<div>
				<h3 class="text-base font-semibold mb-4">{t('footer.product')}</h3>
				<ul class="space-y-2 text-sm">
					<li><a href="/pricing" class="text-muted-foreground hover:text-foreground transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded px-1">{t('footer.pricing')}</a></li>
					<li><a href="/docs" class="text-muted-foreground hover:text-foreground transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded px-1">{t('footer.documentation')}</a></li>
					<li><a href="/upload" class="text-muted-foreground hover:text-foreground transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded px-1">{t('footer.upload')}</a></li>
				</ul>
			</div>
		</div>
		<div class="mt-8 pt-8 border-t text-center text-sm text-muted-foreground">
			<p>&copy; {new Date().getFullYear()} {t('footer.copyright')}</p>
		</div>
	</div>
</footer>

