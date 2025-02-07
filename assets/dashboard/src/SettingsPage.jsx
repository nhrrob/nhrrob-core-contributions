import { useState, useEffect } from '@wordpress/element';
import { getSettings, updateSettings } from './api';

const SettingsPage = () => {
  const [loading, setLoading] = useState(false);
  const [notification, setNotification] = useState(null);
  const [formData, setFormData] = useState({
    username: '',
    preset: 'default',
    cacheDuration: '3600',
    postsPerPage: '10',
  });
  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};
    if (!formData.username.trim()) {
      newErrors.username = 'Username is required';
    }
    if (!formData.postsPerPage || parseInt(formData.postsPerPage) < 1) {
      newErrors.postsPerPage = 'Must be a positive number';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSave = async () => {
    if (!validateForm()) return;

    setLoading(true);
    try {
      const response = await updateSettings(formData);

      setNotification({
        type: 'success',
        message: response.message
      });
    } catch (error) {
      setNotification({
        type: 'error',
        message: 'Error saving settings. Please try again later.'
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const loadSettings = async () => {
      try {
        const data = await getSettings();

        setFormData(data);
      } catch (error) {
        setNotification({
          type: 'error',
          message: 'Error loading settings'
        });
      }
    };

    loadSettings();
  }, []);

  return (
    <div className="nhrcc-core-contributions-settings-wrap">
      <h1 className="wp-heading-inline">Core Contributions Settings</h1>
      
      {notification && (
        <div className={`notice notice-${notification.type === 'success' ? 'success' : 'error'} is-dismissible`}>
          <p>{notification.message}</p>
          <button 
            type="button" 
            className="notice-dismiss"
            onClick={() => setNotification(null)}
          >
            <span className="screen-reader-text">Dismiss this notice</span>
          </button>
        </div>
      )}

      <div className="card">
        <h2>User Settings</h2>
        <p className="description">Configure the default user and display preferences</p>
        <table className="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label htmlFor="username">Default WordPress.org Username</label>
              </th>
              <td>
                <input
                  type="text"
                  id="username"
                  className="regular-text"
                  value={formData?.username}
                  onChange={(e) => setFormData({...formData, username: e.target.value})}
                />
                {errors.username && (
                  <p className="description error">{errors.username}</p>
                )}
                <p className="description">This username will be used when no specific user is provided</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div className="card">
        <h2>Cache Settings</h2>
        <p className="description">Manage how long contribution data is stored</p>
        <table className="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label htmlFor="cacheDuration">Cache Duration</label>
              </th>
              <td>
                <select
                  id="cacheDuration"
                  value={formData?.cacheDuration}
                  onChange={(e) => setFormData({...formData, cacheDuration: e.target.value})}
                >
                  <option value="1800">30 Minutes</option>
                  <option value="3600">1 Hour</option>
                  <option value="86400">24 Hours</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div className="card">
        <h2>Display Settings</h2>
        <p className="description">Customize how contributions are displayed</p>
        <table className="form-table">
          <tbody>
            <tr>
              <th scope="row">
                <label htmlFor="preset">Preset</label>
              </th>
              <td>
                <select
                  id="preset"
                  value={formData?.preset}
                  onChange={(e) => setFormData({...formData, preset: e.target.value})}
                >
                  <option value="default">Default</option>
                  <option value="minimal">Minimal</option>
                </select>
              </td>
            </tr>

            <tr>
              <th scope="row">
                <label htmlFor="postsPerPage">Posts Per Page</label>
              </th>
              <td>
                <input
                  type="number"
                  id="postsPerPage"
                  className="small-text"
                  value={formData?.postsPerPage}
                  onChange={(e) => setFormData({...formData, postsPerPage: e.target.value})}
                />
                {errors.postsPerPage && (
                  <p className="description error">{errors.postsPerPage}</p>
                )}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p className="submit">
        <button 
          type="button" 
          className="button button-primary"
          onClick={handleSave}
          disabled={loading}
        >
          {loading ? 'Saving...' : 'Save Changes'}
        </button>
      </p>
    </div>
  );
};

export default SettingsPage;