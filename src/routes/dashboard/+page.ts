import type { PageLoad } from './$types';
import { browser } from '$app/environment';

export const load: PageLoad = async ({ parent, fetch, depends }) => {
	// Mark this page as dependent on dashboard data for invalidation
	depends('app:dashboard');
	
	// Don't block SSR - all data loading happens client-side
	// This prevents SSR from hanging on API calls
	return {
		// Return nothing - component handles all loading client-side
	};
};

