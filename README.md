# Heart-Disease-AI-FYP

## Environment Setup

This project uses environment variables to manage database configuration and other sensitive information.

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL database

### Installation

1. Clone the repository
2. Install dependencies:
   ```
   composer install
   ```
3. Configure your environment:
   - The project includes a `.env` file with default configuration
   - Modify the values in the `.env` file to match your environment:
     ```
     DB_HOST=localhost
     DB_NAME=heart_disease_db
     DB_USER=root
     DB_PASS=your_password
     DB_CHARSET=utf8mb4
     ```

### Database Setup

1. Create a MySQL database named `heart_disease_db` (or the name specified in your `.env` file)
2. Import the database schema (if available)

### Running the Application

1. Start your web server
2. Navigate to the project in your web browser

## Security Notes

- Never commit the `.env` file with real credentials to version control
- For production, ensure you set up proper database credentials with limited permissions