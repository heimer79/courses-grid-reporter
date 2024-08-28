import { useState, useEffect, useCallback } from '@wordpress/element';
import { InspectorControls, BlockControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToolbarGroup, ToolbarButton } from '@wordpress/components';

const CoursesGridEdit = ({ attributes, setAttributes }) => {
    const [statusMessage, setStatusMessage] = useState('Loading...');
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);
    const blockProps = useBlockProps({
        className: 'p-4 bg-white rounded-lg shadow-md'
    });
    
    const fetchData = useCallback(() => {
        if (attributes.endpointUrl) {
            setIsLoading(true);
            setIsError(false);
            setStatusMessage('Loading...');
            const proxyUrl = `${window.location.origin}/wp-admin/admin-ajax.php?action=proxy_request_to_api&api_url=${encodeURIComponent(attributes.endpointUrl)}`;

            fetch(proxyUrl)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        try {
                            const courses = JSON.parse(data.data);
                            setStatusMessage(courses.length === 0 ? 'No data found' : 'Data displayed in front end');
                            setIsError(courses.length === 0);
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                            setStatusMessage('Error parsing data');
                            setIsError(true);
                        }
                    } else {
                        setStatusMessage('Error fetching data');
                        setIsError(true);
                    }
                })
                .catch((error) => {
                    console.error('Error fetching courses:', error);
                    setStatusMessage('Error connecting to the API');
                    setIsError(true);
                })
                .finally(() => {
                    setIsLoading(false);
                });
        } else {
            setStatusMessage('No endpoint URL provided');
            setIsError(true);
        }
    }, [attributes.endpointUrl]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    return (
        <div {...blockProps}>
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon="grid-view"
                        label="Grid View"
                        onClick={() => setAttributes({ viewMode: 'grid' })}
                    />
                    <ToolbarButton
                        icon="list-view"
                        label="List View"
                        onClick={() => setAttributes({ viewMode: 'list' })}
                    />
                    <ToolbarButton
                        icon="update"
                        label="Refresh Data"
                        onClick={fetchData}
                        disabled={isLoading}
                    />
                </ToolbarGroup>
            </BlockControls>
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
                <p className={`text-lg font-semibold ${
                    isLoading ? 'text-blue-600' :
                    isError ? 'text-red-600' : 'text-green-600'
                }`}>
                    {statusMessage}
                </p>
                {isLoading && <p className="text-blue-600 text-lg">Refreshing data...</p>}
            </div>
        </div>
    );
};

export default CoursesGridEdit;