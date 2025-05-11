# NOX E-commerce Platform

NOX is a premium clothing and lifestyle e-commerce platform built with PHP and JSON-based data storage.

## Features

- User authentication and authorization
- Product catalog with categories
- Shopping cart functionality
- Order management
- Payment processing
- Inventory management
- Reseller management
- Employee management
- Branch management
- Company profile management

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository
2. Configure your web server to point to the project directory
3. Ensure write permissions for the `data/` directory
4. Access the application through your web browser

## Directory Structure

```
├── index.php                # Homepage 
├── assets/                  # Frontend assets
├── classes/                 # PHP classes
├── data/                    # JSON data storage
├── includes/               # Core functionality
└── .vscode/                # VS Code configuration
```

## Configuration

Edit `includes/config.php` to set up:
- Site information
- File paths
- Currency settings
- Shipping methods
- Tax rates

## Security

- All user passwords are hashed
- Input data is sanitized
- Session management implemented
- JSON data files are outside web root

## License

MIT License

## Support

For support, please open an issue in the repository.