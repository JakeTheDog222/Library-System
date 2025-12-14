Library System (Modern UI)
==========================
Files included:
- sql/schema.sql            -> Database schema + seed data
- config.php                -> DB connection (edit credentials)
- install_seed.php         -> create admin/student accounts
- cron/reminders.php       -> CLI script to show due/overdue reminders
- helpers.php              -> common helpers
- index.php                -> combined login page (student/admin)
- logout.php
- classes/Database.php
- classes/User.php
- classes/Book.php
- classes/Borrow.php
- admin/* and student/* pages
- assets/css/custom.css
- assets/img/ (empty)

Quick setup:
1. Import sql/schema.sql into MySQL (or run install_seed.php after creating DB).
2. Edit config.php with database credentials.
3. Place folder in your webroot and visit index.php.
4. Run cron/reminders.php daily via CLI to list/send reminders.


Design a modern, aesthetically pleasing card layout for a “Book Genres” section.

Requirements:
- Each genre should be displayed inside a card component.
- Use a clean, minimalist, modern UI style with subtle shadows, smooth border-radius, and balanced spacing.
- Title text (genre name) should be bold and slightly larger, with a sleek underline or accent bar.
- Show two text fields inside each card:
  • Books: #
  • Borrow Rate: #
- Font should look modern and clean (e.g., Inter, Poppins, or system default).
- Add smooth hover effects: slight scale-up, stronger shadow, and soft transition.
- Cards should align in a responsive grid: 4 columns on desktop, 2 on tablet, 1 on mobile.
- Overall spacing should be airy: 20–30px padding inside cards, 20–40px gap between cards.
- Background should be soft and light (e.g. #f9fafb).
- Cards should look premium/library-like but modern — white background, gold or accent line under titles.
- Provide HTML + CSS that matches the description.