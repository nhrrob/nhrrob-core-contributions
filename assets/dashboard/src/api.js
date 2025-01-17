import apiFetch from '@wordpress/api-fetch';

export const getSettings = async () => {
    try {
        return await apiFetch({ 
            path: 'core-contributions/v1/settings'
        });
    } catch (error) {
        console.error('Error fetching settings:', error);
        throw error;
    }
};

export const updateSettings = async (settings) => {
    try {
        return await apiFetch({
            path: 'core-contributions/v1/settings',
            method: 'POST',
            data: settings
        });
    } catch (error) {
        console.error('Error updating settings:', error);
        throw error;
    }
};