import { useState, useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';

const CoursesGridEdit = ({ attributes, setAttributes }) => {
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (attributes.endpointUrl) {
            fetch(attributes.endpointUrl)
                .then((response) => response.json())
                .then((data) => {
                    setCourses(data);
                    setLoading(false);
                })
                .catch((error) => {
                    console.error('Error fetching courses:', error);
                    setLoading(false);
                });
        }
    }, [attributes.endpointUrl]);

    const handleButtonClick = () => {
        const availableCourses = courses.filter(course => course.workflow_state === 'available');
        alert(`There are ${availableCourses.length} courses available.`);
    };

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
                {loading ? (
                    <p>Loading courses...</p>
                ) : (
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course Code</th>
                                <th>Workflow State</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {courses.map((course) => (
                                <tr key={course.id}>
                                    <td>{course.id}</td>
                                    <td>{course.name}</td>
                                    <td>{course.course_code}</td>
                                    <td>{course.workflow_state}</td>
                                    <td>{course.start_at}</td>
                                    <td>{course.end_at}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
                <Button isPrimary onClick={handleButtonClick}>
                    Generate Report
                </Button>
            </div>
        </>
    );
};

export default CoursesGridEdit;
