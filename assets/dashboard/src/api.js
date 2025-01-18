import apiFetch from '@wordpress/api-fetch';

export const getSettings = async () => {
    try {

        let settings = await apiFetch({ 
            path: '/nhrcc-core-contributions/v1/settings',
        });

        console.log(settings);

        return settings;
    } catch (error) {
        console.error('Error fetching settings:', error);
        throw error;
    }
};

export const updateSettings = async (settings, nonce) => {
    try {
        return await apiFetch({
            path: '/nhrcc-core-contributions/v1/settings',
            method: 'POST',
            data: {
                ...settings,
                _wpnonce: nonce // Add the nonce in the request data
            }
        });
    } catch (error) {
        console.error('Error updating settings:', error);
        throw error;
    }
};