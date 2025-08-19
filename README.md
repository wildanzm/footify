# Footify - Diabetic Foot Screening Application

Footify is a web-based application designed to assist healthcare professionals in performing a quick, accurate, and documented screening for diabetic feet. It is based on the standardized **Inlow’s 60-Second Diabetic Foot Screen** to automatically classify risk levels and provide care recommendations.

## About The Project

The main goal of Footify is to streamline the diabetic foot screening process. By digitizing the Inlow's 60-second method, the application guides healthcare providers through a systematic assessment, minimizing guesswork and ensuring all critical risk factors are evaluated. The system automatically calculates a risk score based on the inputs and provides a clear risk classification, helping to standardize patient care.

This project was built as a modern, single-page-like experience, ensuring a fast and responsive user interface for efficient data entry.

## Key Features ✨

  * **Multi-Step Screening Form:** A guided, step-by-step wizard that walks the user through all stages of the Inlow’s 60-second diabetic foot screen, from patient identity to detailed physical examination.
  * **Automated Risk Classification:** The application automatically processes the screening data to classify the patient's risk level into categories such as Very Low Risk, Low Risk, Moderate Risk, High Risk, and Urgent.
  * **Dynamic Recommendations:** Based on the final risk classification, the system suggests appropriate care actions and follow-up schedules.
  * **Clean & Responsive UI:** The user interface is designed to be clean, minimalist, and fully responsive, working seamlessly on desktops, tablets, and mobile devices.

## Tech Stack 🛠️

This project is built on a modern TALL stack, leveraging the power of the Laravel ecosystem.

  * **[Laravel](https://laravel.com/)**: The core PHP framework providing a robust backend structure.
  * **[Livewire](https://livewire.laravel.com/)**: For building dynamic, single-page-like interfaces without leaving PHP.
  * **[Alpine.js](https://alpinejs.dev/)**: A rugged, minimal JavaScript framework for client-side interactivity.
  * **[Tailwind CSS](https://tailwindcss.com/)**: A utility-first CSS framework for rapid UI development.
  * **[Flowbite](https://flowbite.com/)**: An open-source component library built on top of Tailwind CSS.
  * **MySQL**: The relational database used for data storage.

## Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

Make sure you have the following installed on your local machine:

  * PHP 8.2 or higher
  * Composer
  * Node.js & npm
  * A local database server (e.g., MySQL)

### Installation

1.  **Clone the repository:**

    ```sh
    git clone https://github.com/your_username/footify.git
    cd footify
    ```

2.  **Install PHP dependencies:**

    ```sh
    composer install
    ```

3.  **Install JavaScript dependencies:**

    ```sh
    npm install && npm run build
    ```

4.  **Set up your environment file:**

      * Copy the example environment file:
        ```sh
        cp .env.example .env
        ```
      * Generate a new application key:
        ```sh
        php artisan key:generate
        ```

5.  **Configure your `.env` file:**
    Open the `.env` file and update the `DB_*` variables with your local database credentials:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=footify
    DB_USERNAME=root
    DB_PASSWORD=your_password
    ```

    Ensure you have created a database named `footify`.

6.  **Run the database migrations:**
    This will create all the necessary tables (`users`, `patients`, `screenings`, etc.).

    ```sh
    php artisan migrate
    ```

7.  **Serve the application:**

    ```sh
    php artisan serve
    ```

    The application will be available at `http://127.0.0.1:8000`.
