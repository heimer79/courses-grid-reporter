<?php
function render_courses_grid_reporter_block( $attributes ) {
    $endpoint_url = $attributes['endpointUrl'];

    // Fetch the JSON data from the endpoint
    $response = wp_remote_get( esc_url( $endpoint_url ) );
    if ( is_wp_error( $response ) ) {
        error_log('Courses Grid: Error fetching data - ' . $response->get_error_message());
        return '<p class="text-red-500">Unable to retrieve courses data.</p>';
    }

    $body = wp_remote_retrieve_body( $response );
    $courses = json_decode( $body, true );

    // Log the raw data for debugging
    error_log('Courses Grid: Raw data received - ' . print_r($courses, true));

    if ( !is_array( $courses ) || empty( $courses ) ) {
        error_log('Courses Grid: Invalid or empty data received');
        return '<p class="text-red-500">No courses found or invalid data received.</p>';
    }

    // Filter and count available courses
    $available_courses = array_filter($courses, function($course) {
        return isset($course['workflow_state']) && $course['workflow_state'] === 'available';
    });
    $available_count = count($available_courses);

    // Log the count of available courses
    error_log('Courses Grid: Number of available courses - ' . $available_count);

    ob_start();
    ?>
    <div class="courses-grid w-full overflow-x-auto shadow-md rounded-lg relative">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
                <tr>
                    <?php
                    $headers = ['ID', 'Name', 'Course Code', 'Workflow State', 'Start Date', 'End Date'];
                    foreach ($headers as $index => $header) :
                    ?>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200 group" onclick="sortTable(<?php echo $index; ?>)">
                        <div class="flex items-center">
                            <?php echo esc_html($header); ?>
                            <span class="ml-2 invisible group-hover:visible">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ( $courses as $course ) : ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['id'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900"><?php echo esc_html( $course['name'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900"><?php echo esc_html( $course['course_code'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['workflow_state'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['start_at'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['end_at'] ?? 'N/A' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="absolute bottom-2 right-2 text-2xl text-blue-500 animate-bounce">→</div>
    </div>
    <div class="mt-4 flex justify-center">
        <button id="report-button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Generate Report
        </button>
    </div>

    <script>
    let currentSortColumn = -1;
    let isAscending = true;

    function sortTable(columnIndex) {
        const table = document.querySelector(".courses-grid table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const headers = table.querySelectorAll("th");

        // Reset all headers
        headers.forEach(header => {
            header.querySelector('svg').classList.remove('text-blue-500');
            header.querySelector('svg').classList.add('text-gray-400');
            header.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>';
        });

        // Toggle sort direction if clicking on the same column
        if (currentSortColumn === columnIndex) {
            isAscending = !isAscending;
        } else {
            isAscending = true;
        }

        currentSortColumn = columnIndex;

        // Update the clicked header
        const clickedHeader = headers[columnIndex];
        clickedHeader.querySelector('svg').classList.remove('text-gray-400');
        clickedHeader.querySelector('svg').classList.add('text-blue-500');
        clickedHeader.querySelector('svg').innerHTML = isAscending
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';

        const sortedRows = rows.sort((a, b) => {
            const aText = a.children[columnIndex].innerText.toLowerCase();
            const bText = b.children[columnIndex].innerText.toLowerCase();
            return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });

        tbody.innerHTML = '';
        sortedRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('report-button').addEventListener('click', function() {
        const availableCoursesCount = <?php echo $available_count; ?>;
        alert(`There are ${availableCoursesCount} courses available.`);
    });
    </script>
    <?php
    return ob_get_clean();
}