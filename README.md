# Hireflix Clone

A one-way video interview web application built with Laravel and MySQL. Features include role-based authentication (Admin, Candidate, Reviewer), interview creation, candidate video submissions, and reviewer scoring.

## Features

- **Role-based Authentication**: Admin, Reviewer, and Candidate roles
- **Interview Management**: Create and manage video interviews with custom questions
- **Video Submissions**: Candidates can submit video/audio/text responses
- **Review System**: Reviewers can score and comment on submissions
- **Admin Dashboard**: Comprehensive analytics and management tools
- **Real-time Updates**: Dynamic dashboard with AJAX-powered data loading

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.1 or higher
- Composer
- MySQL or SQLite
- Web server (Apache/Nginx) or PHP built-in server

## Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/bhaskerreddy2018/hireflix_clone
cd Hireflix_clone
```

### 2. Install PHP Dependencies
```bash
cd laravel_app
composer install
```

### 3. Environment Configuration
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

#### Option A: Using SQLite (Recommended for Development)
The application comes with SQLite configured by default. No additional setup required.

#### Option B: Using MySQL
1. Create a new MySQL database named `hireflix_clone`
2. Update the `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hireflix_clone
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Database Migrations
```bash
php artisan migrate
```

### 6. Start the Application
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Access and Login

### Login URL
- **Main Login**: `http://localhost:8000/login`
- **Registration**: `http://localhost:8000/register`

### User Roles and Access

#### 1. Admin Access
- **URL**: `http://localhost:8000/login`
- **Features**: 
  - Create and manage interviews
  - View all candidates and reviewers
  - Access comprehensive analytics
  - Manage all submissions and scores
- **Sample Credentials** (create via registration):
  - Email: `admin@hireflix.com`
  - Password: `admin123`
  - Role: `admin`

#### 2. Reviewer Access
- **URL**: `http://localhost:8000/login`
- **Features**:
  - Review candidate submissions
  - Score and comment on responses
  - View reviewer statistics
- **Sample Credentials** (create via registration):
  - Email: `reviewer@hireflix.com`
  - Password: `reviewer123`
  - Role: `reviewer`

#### 3. Candidate Access
- **URL**: `http://localhost:8000/login`
- **Features**:
  - View available interviews
  - Submit video/audio/text responses
  - Track submission progress
- **Sample Credentials** (create via registration):
  - Email: `candidate@hireflix.com`
  - Password: `candidate123`
  - Role: `candidate`

## Creating Test Users

Since the application uses registration for user creation, you can create test users by:

1. Visit `http://localhost:8000/register`
2. Fill in the registration form with:
   - Name
   - Email
   - Password (minimum 4 characters)
   - Confirm Password
   - Role (admin, reviewer, or candidate)

## Application Structure

### Database Tables
- `users` - User accounts with role-based access
- `interviews` - Interview sessions created by admins/reviewers
- `questions` - Questions within each interview
- `submissions` - Candidate responses to questions
- `scores` - Reviewer scores and comments

### Key Features by Role

#### Admin Dashboard
- Total interviews, candidates, reviewers, and submissions
- Detailed candidate management
- Reviewer performance tracking
- Comprehensive submission analytics

#### Reviewer Dashboard
- Pending reviews count
- Completed reviews tracking
- Average scoring statistics
- Submission review interface

#### Candidate Dashboard
- Available interviews list
- Submission progress tracking
- Video/audio recording interface
- Response submission system

## File Upload Configuration

The application supports file uploads for video and audio submissions:
- **Video formats**: webm, mp4, avi, mov (max 100MB)
- **Audio formats**: webm, mp3, wav (max 50MB)
- **Storage**: Files are stored in `storage/app/public/submissions/`

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure storage directory is writable
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. **Database Connection**: Verify database credentials in `.env` file

3. **Application Key**: Ensure you've run `php artisan key:generate`

4. **File Uploads**: Check PHP upload limits in `php.ini`

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

