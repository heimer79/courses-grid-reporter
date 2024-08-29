import { useState, useEffect, useCallback } from '@wordpress/element';
import { InspectorControls, BlockControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToolbarGroup, ToolbarButton, Button } from '@wordpress/components';

/**
 * The edit component for the Courses Grid block.
 * 
 * This component handles the user interface and logic for configuring and displaying
 * the courses grid in the block editor. It provides controls for setting the endpoint URL,
 * the report email, and refreshing the data.
 *
 * @param {Object} props - The component properties.
 * @param {Object} props.attributes - The block attributes.
 * @param {Function} props.setAttributes - The function to update block attributes.
 * @return {JSX.Element} The rendered edit component.
 */
const CoursesGridEdit = ({ attributes, setAttributes }) => {
    // Local state management
    const [statusMessage, setStatusMessage] = useState('Loading...');
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);
    const [courseCount, setCourseCount] = useState(0);
    const [reportStatus, setReportStatus] = useState('');

    // Block properties, including custom class names
    const blockProps = useBlockProps({
        className: 'p-4 bg-white rounded-lg shadow-md'
    });

    /**
     * Fetches data from the endpoint URL and updates the component state.
     * 
     * The data is fetched from a proxy URL that forwards the request to the API.
     * If successful, the number of courses is counted and displayed.
     */
    const fetchData = useCallback(() => {
        if (attributes.endpointUrl) {
            setIsLoading(true);
            setIsError(false);
            setStatusMessage('Loading...');
            
            // Construct the proxy URL for the AJAX request
            const proxyUrl = `${window.location.origin}/wp-admin/admin-ajax.php?action=proxy_request_to_api&api_url=${encodeURIComponent(attributes.endpointUrl)}`;

            fetch(proxyUrl)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        try {
                            const courses = JSON.parse(data.data);
                            setCourseCount(courses.length);
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

    /**
     * Sends a report of the course count to the specified email.
     * 
     * The report is sent via an AJAX request, and the status of the operation is
     * updated in the component's state.
     */
    const sendReport = () => {
        if (!attributes.reportEmail) {
            setReportStatus('No email address provided');
            return;
        }

        setReportStatus('Sending report...');
        
        // Prepare the data for the AJAX request
        const reportData = {
            action: 'send_courses_report',
            count: courseCount,
            email: attributes.reportEmail
        };

        // Send the report via a POST request to the WordPress admin-ajax.php endpoint
        fetch(window.location.origin + '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(reportData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setReportStatus('Report sent successfully!');
            } else {
                setReportStatus('Error sending report. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            setReportStatus('An error occurred while sending the report.');
        });
    };

    // Fetch data on component mount and when the endpoint URL changes
    useEffect(() => {
        fetchData();
    }, [fetchData]);

    return (
        <div {...blockProps}>
            {/* Block controls toolbar */}
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

            {/* Inspector controls panel */}
            <InspectorControls>
                <PanelBody title="Endpoint Settings">
                    <TextControl
                        label="Endpoint URL"
                        value={attributes.endpointUrl}
                        onChange={(value) => setAttributes({ endpointUrl: value })}
                    />
                    <TextControl
                        label="Report Email"
                        value={attributes.reportEmail}
                        onChange={(reportEmail) => setAttributes({ reportEmail })}
                    />
                    <Button
                        isPrimary
                        onClick={sendReport}
                        disabled={!attributes.reportEmail || isLoading}
                    >
                        Send Report
                    </Button>
                    {reportStatus && (
                        <p className={`mt-2 ${reportStatus.includes('Error') ? 'text-red-600' : 'text-green-600'}`}>
                            {reportStatus}
                        </p>
                    )}
                </PanelBody>
            </InspectorControls>

            {/* Display course data and status messages */}
            <div className="courses-grid">
                <p className={`text-lg font-semibold ${
                    isLoading ? 'text-blue-600' :
                    isError ? 'text-red-600' : 'text-green-600'
                }`}>
                    {statusMessage}
                </p>
                {isLoading && <p className="text-blue-600 text-lg">Refreshing data...</p>}
                <p className="mt-2">Total courses: {courseCount}</p>
            </div>
        </div>
    );
};

export default CoursesGridEdit;
