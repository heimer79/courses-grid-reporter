import { useState, useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

const CoursesGridEdit = ({ attributes, setAttributes }) => {
    const [statusMessage, setStatusMessage] = useState('Loading...');
    
    useEffect(() => {
        if (attributes.endpointUrl) {
            // Construct the URL for the proxy script
            const proxyUrl = `${window.location.origin}/wp-admin/admin-ajax.php?action=proxy_request_to_api&api_url=${encodeURIComponent(attributes.endpointUrl)}`;

            fetch(proxyUrl)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        setStatusMessage('Data displayed');
                        const courses = JSON.parse(data.data);
                        if (courses.length === 0) {
                            setStatusMessage('No data found');
                        }
                    } else {
                        setStatusMessage('Error fetching data');
                    }
                })
                .catch((error) => {
                    console.error('Error fetching courses:', error);
                    setStatusMessage('Error connecting to the API');
                });
        }
    }, [attributes.endpointUrl]);

    return (
        <>
            <InspectorControls>
                <PanelBody title="Endpoint Settings">
                    <TextControl
                        label="Endpoint URL"
                        value={attributes.endpointUrl}
                        onChange={(value) => setAttributes({ endpointUrl: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div className="courses-grid">
                <p>{statusMessage}</p>
            </div>
        </>
    );
};

export default CoursesGridEdit;
