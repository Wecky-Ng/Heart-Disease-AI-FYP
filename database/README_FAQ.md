# FAQ Feature Documentation

## Overview
The FAQ (Frequently Asked Questions) feature displays questions and answers about heart disease and the prediction system. The content is dynamically loaded from the database, with each FAQ item displayed in its own card.

## Database Structure
The FAQ data is stored in the `faq` table with the following structure:
- `id`: Unique identifier (auto-increment)
- `faq_title`: The question title (VARCHAR 100)
- `detail`: The answer text (VARCHAR 200)

## Sample Data
A sample SQL file with FAQ data is provided in `database/sample_faq_data.sql`. This file contains 8 common questions and answers related to heart disease and the prediction system.

## How to Import Sample Data

### Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < database/sample_faq_data.sql
```

### Using phpMyAdmin
1. Open phpMyAdmin and select your database
2. Click on the "Import" tab
3. Browse for the `database/sample_faq_data.sql` file
4. Click "Go" to import the data

## Accessing the FAQ Page
The FAQ page is accessible from the sidebar menu. Users can click on the "FAQ" link to view all frequently asked questions.

## Customizing FAQ Content
To add, edit, or remove FAQ items, you can:
1. Use the database management tool to modify the `faq` table directly
2. Create an admin interface to manage FAQ content (not included in current implementation)

## Page Structure
- Each FAQ is displayed in a card layout (2 cards per row on large screens)
- Cards have a hover effect for better user interaction
- Empty state handling is included if no FAQs are available in the database