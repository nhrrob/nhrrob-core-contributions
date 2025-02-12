import { useState, useEffect, createElement } from '@wordpress/element';
import { getSettings, updateSettings } from './api';

const SettingsPage = () => {
  const [loading, setLoading] = useState(false);
  const [notification, setNotification] = useState(null);
  const [formData, setFormData] = useState({
    username: '',
    preset: 'default',
    cacheDuration: 43200, // Ensure default is valid
    postsPerPage: 10, // Ensure number type
  });
  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};
    if (!formData.username.trim()) {
      newErrors.username = 'Username is required';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSave = async () => {
    if (!validateForm()) return;

    setLoading(true);
    try {
      const response = await updateSettings(formData);
      setNotification({ type: 'success', message: response.message });
    } catch (error) {
      setNotification({ type: 'error', message: 'Error saving settings. Please try again later.' });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const loadSettings = async () => {
      try {
        const data = await getSettings();
        setFormData({
          ...data,
          cacheDuration: Number(data.cacheDuration) || 43200, // Ensure number
          postsPerPage: Number(data.postsPerPage) || 10, // Ensure number
        });
      } catch (error) {
        setNotification({ type: 'error', message: 'Error loading settings' });
      }
    };

    loadSettings();
  }, []);

  return createElement(
    'div',
    { className: 'nhrcc-core-contributions-settings-wrap' },
    createElement('h1', { className: 'wp-heading-inline' }, 'Core Contributions Settings'),
    notification &&
      createElement(
        'div',
        { className: `notice notice-${notification.type === 'success' ? 'success' : 'error'} is-dismissible` },
        createElement('p', null, notification.message),
        createElement(
          'button',
          { type: 'button', className: 'notice-dismiss', onClick: () => setNotification(null) },
          createElement('span', { className: 'screen-reader-text' }, 'Dismiss this notice')
        )
      ),
    createElement(
      'div',
      { className: 'card' },
      createElement('h2', null, 'User Settings'),
      createElement('p', { className: 'description' }, 'Configure the default user and display preferences'),
      createElement(
        'table',
        { className: 'form-table' },
        createElement('tbody', null,
          createElement('tr', null,
            createElement('th', { scope: 'row' },
              createElement('label', { htmlFor: 'username' }, 'Default WordPress.org Username')
            ),
            createElement('td', null,
              createElement('input', {
                type: 'text',
                id: 'username',
                className: 'regular-text',
                value: formData.username,
                onChange: (e) => setFormData({ ...formData, username: e.target.value })
              }),
              errors.username && createElement('p', { className: 'description error' }, errors.username),
              createElement('p', { className: 'description' }, 'This username will be used when no specific user is provided')
            )
          )
        )
      )
    ),
    createElement(
      'div',
      { className: 'card' },
      createElement('h2', null, 'Display Settings'),
      createElement('p', { className: 'description' }, 'Customize how contributions are displayed'),
      createElement(
        'table',
        { className: 'form-table' },
        createElement('tbody', null,
          createElement('tr', null,
            createElement('th', { scope: 'row' },
              createElement('label', { htmlFor: 'preset' }, 'Preset')
            ),
            createElement('td', null,
              createElement('select', {
                id: 'preset',
                value: formData.preset,
                onChange: (e) => setFormData({ ...formData, preset: e.target.value })
              },
                createElement('option', { value: 'default' }, 'Default'),
                createElement('option', { value: 'minimal' }, 'Minimal')
              )
            )
          ),
          createElement('tr', { className: 'hidden' },
            createElement('th', { scope: 'row' },
              createElement('label', { htmlFor: 'postsPerPage' }, 'Posts Per Page')
            ),
            createElement('td', null,
              createElement('input', {
                type: 'number',
                id: 'postsPerPage',
                className: 'small-text',
                value: formData.postsPerPage,
                onChange: (e) => setFormData({ ...formData, postsPerPage: Number(e.target.value) })
              }),
              errors.postsPerPage && createElement('p', { className: 'description error' }, errors.postsPerPage)
            )
          )
        )
      )
    ),
    createElement(
      'div',
      { className: 'card' },
      createElement('h2', null, 'Cache Settings'),
      createElement('p', { className: 'description' }, 'Manage how long contribution data is stored'),
      createElement(
        'table',
        { className: 'form-table' },
        createElement('tbody', null,
          createElement('tr', null,
            createElement('th', { scope: 'row' },
              createElement('label', { htmlFor: 'cacheDuration' }, 'Cache Duration')
            ),
            createElement('td', null,
              createElement('select', {
                id: 'cacheDuration',
                value: formData.cacheDuration,
                onChange: (e) => setFormData({ ...formData, cacheDuration: Number(e.target.value) })
              },
                createElement('option', { value: '1800' }, '30 Minutes'),
                createElement('option', { value: '3600' }, '1 Hour'),
                createElement('option', { value: '21600' }, '6 Hours'),
                createElement('option', { value: '43200' }, '12 Hours'),
                createElement('option', { value: '86400' }, '24 Hours')
              )
            )
          )
        )
      )
    ),
    createElement(
      'p',
      { className: 'submit' },
      createElement('button', {
        type: 'button',
        className: 'button button-primary',
        onClick: handleSave,
        disabled: loading
      }, loading ? 'Saving...' : 'Save Changes')
    )
  );
};

export default SettingsPage;
