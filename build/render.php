<?php
function render_courses_grid_reporter_block( $attributes ) {
    
    $endpoint_url = $attributes['endpointUrl'];

    // Fetch the JSON data from the endpoint
    $response = wp_remote_get( esc_url( $endpoint_url ) );
    if ( is_wp_error( $response ) ) {
        return '<p>Unable to retrieve courses data.</p>';
    }

    $courses = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $courses ) || !is_array( $courses ) ) {
        return '<p>No courses found.</p>';
    }

    ob_start();
    ?>
    <div class="courses-grid w-full overflow-x-auto">
    <div class="min-w-full bg-gray-100 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">
        <div class="grid grid-cols-6 bg-gray-200">
            <div class="p-2 cursor-pointer hover:text-blue-500">ID</div>
            <div class="p-2 cursor-pointer hover:text-blue-500">Name</div>
            <div class="p-2 cursor-pointer hover:text-blue-500">Course Code</div>
            <div class="p-2 cursor-pointer hover:text-blue-500">Workflow State</div>
            <div class="p-2 cursor-pointer hover:text-blue-500">Start Date</div>
            <div class="p-2 cursor-pointer hover:text-blue-500">End Date</div>
        </div>
    </div>
    <div class="bg-white divide-y divide-gray-200">
        <?php foreach ( $courses as $course ) : ?>
            <div class="grid grid-cols-6">
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['id'] ); ?></div>
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['name'] ); ?></div>
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['course_code'] ); ?></div>
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['workflow_state'] ); ?></div>
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['start_at'] ); ?></div>
                <div class="p-2 text-gray-900"><?php echo esc_html( $course['end_at'] ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <?php
    return ob_get_clean();
}
