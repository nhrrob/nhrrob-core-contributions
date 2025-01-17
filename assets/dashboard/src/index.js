import { createRoot } from '@wordpress/element';
import SettingsPage from './SettingsPage';


// Initialize the app
const init = () => {
    const container = document.getElementById('nhrcc-admin-settings');
    if (container) {
        const root = createRoot(container);
        root.render(<SettingsPage />);
    }
};

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}