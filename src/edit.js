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

    const sortTable = (columnIndex) => {
        const table = document.querySelector(".courses-grid table");
        const rows = Array.from(table.rows).slice(1);
        const sortedRows = rows.sort((a, b) => {
            const aText = a.cells[columnIndex].innerText.toLowerCase();
            const bText = b.cells[columnIndex].innerText.toLowerCase();
            return aText.localeCompare(bText);
        });
        table.tBodies[0].append(...sortedRows);
    };

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
                                <th onClick={() => sortTable(0)}>ID</th>
                                <th onClick={() => sortTable(1)}>Name</th>
                                <th onClick={() => sortTable(2)}>Course Code</th>
                                <th onClick={() => sortTable(3)}>Workflow State</th>
                                <th onClick={() => sortTable(4)}>Start Date</th>
                                <th onClick={() => sortTable(5)}>End Date</th>
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
