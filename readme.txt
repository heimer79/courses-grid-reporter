=== Courses Grid Reporter ===
Contributors:      The WordPress Contributors
Tags:              block
Tested up to:      6.1
Stable tag:        0.1.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Example block scaffolded with Create Block tool.

== Description ==



# Courses Grid Reporter Block Documentation

## Overview

The Courses Grid Reporter is a custom WordPress block that fetches and displays a grid of courses from a specified API endpoint. It also provides functionality to generate and email a report about the available courses.

## Block Structure

The block is composed of several key files:

1. `block.json`: Configuration file for the block.
2. `index.js`: Contains the edit component for the Gutenberg editor.
3. `render.php`: Handles the server-side rendering of the block on the frontend.
4. `view.js`: Manages frontend interactivity.
5. `style.css`: Defines styles for the block.

## Key Features

- Dynamic course data fetching from a configurable API endpoint.
- Tabular display of course information.
- Report generation and email functionality.
- Configurable email address for receiving reports.

## Technical Explanation

### Data Fetching

- The block uses the WordPress REST API to fetch course data from a specified endpoint.
- In the editor, data is fetched using JavaScript's `fetch` API, proxied through WordPress to avoid CORS issues.
- On the frontend, PHP's `wp_remote_get()` function is used to retrieve the data.

### Rendering

- Editor: React is used to render a preview of the block in the Gutenberg editor.
- Frontend: PHP generates the HTML structure for the course grid table.

### Report Generation

- A "Generate Report" button triggers the report generation process.
- An AJAX request is sent to a custom WordPress action (`send_courses_report`).
- The PHP function `send_courses_report()` processes the request and sends an email using `wp_mail()`.

### Block Attributes

- `endpointUrl`: Stores the API endpoint URL for fetching course data.
- `reportEmail`: Stores the email address for sending reports.

### Editor Interface

- The block's edit component (`CoursesGridEdit` in `index.js`) provides UI for configuring the endpoint URL and report email.
- It also displays a preview of the fetched data and the report generation button.

### Styling

- Tailwind CSS classes are used for styling the block in both the editor and frontend.
- Additional custom styles can be added in `style.css`.

## Usage

1. Add the "Courses Grid Reporter" block to a page or post.
2. Configure the API endpoint URL in the block settings.
3. Set the email address for receiving reports.
4. The course data will automatically load and display in a table format.
5. Users can click the "Generate Report" button to send a report to the specified email.

## Development Considerations

- Error handling is implemented for API failures and email sending issues.
- The block uses WordPress coding standards and best practices for security (e.g., data escaping, nonce verification for AJAX calls).
- The block is designed to be responsive and should work well on various screen sizes.

## Future Enhancements

- Implement pagination for large datasets.
- Add sorting and filtering options for the course table.
- Expand report customization options.