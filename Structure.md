/kesmwen/
├── .htaccess
├── config.php
├── composer.json
├── public/
│   ├── index.php
│   ├── css/
│   │   ├── style.css
│   │   └── dark-mode.css
│   ├── js/
│   │   ├── app.js
│   │   ├── chart-config.js
│   │   ├── dark-mode.js
│   │   ├── transactions.js
│   │   └── language-switcher.js
│   └── assets/
│       ├── icons/
│       └── uploads/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── TransactionController.php
│   │   ├── ProfileController.php
│   │   ├── CategoryController.php
│   │   └── ExportController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Transaction.php
│   │   ├── Category.php
│   │   └── Badge.php
│   ├── views/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   ├── dashboard.php
│   │   ├── transactions/
│   │   │   ├── index.php
│   │   │   ├── add.php
│   │   │   └── edit.php
│   │   ├── categories/
│   │   │   ├── index.php
│   │   │   ├── add.php
│   │   │   └── edit.php
│   │   ├── profile.php
│   │   ├── export.php
│   │   └── partials/
│   │       ├── header.php
│   │       ├── footer.php
│   │       ├── sidebar.php
│   │       ├── notifications.php
│   │       └── language-switcher.php
│   └── lib/
│       ├── Database.php
│       ├── Auth.php
│       ├── PdfGenerator.php
│       └── helpers.php
└── lang/
    ├── fr.json
    └── kreyol.json