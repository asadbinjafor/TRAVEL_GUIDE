# Travel Guide 

## Project Scenario Summary

**Travel Guide** is a web-based application that helps people from different countries discover and compare travel destinations around the world. The site provides information about places to visit—including short history, country context, travel options, and estimated cost levels—so visitors can plan trips with more confidence.

The system works like a small travel content platform with three types of registered users and one public (guest) experience:

1. **Scout** — Collects and submits information about visiting places (title, history, country, genre, cost level, how to travel). Submissions go to an admin as **post requests** and appear publicly only after approval.
2. **Admin** — Controls the platform: verifies new accounts (including new admins), approves or rejects scout requests, edits or deletes published posts, and removes inappropriate comments.
3. **General User** — After admin verification, can browse approved destinations, search and filter places, save favorites in a **wishlist**, post comments, and use a simple **trip cost calculator**.
4. **Guest (non-registered)** — Sees a simple home page with encouragement to register or log in; cannot use full features until registered and verified.

**Typical workflow:** A scout submits a place → admin reviews and approves → the post becomes visible to all verified users → general users browse, comment, and save posts to their wishlist.

This project was built as **Web Technologies — Project 01**, following a shared database schema and PHP MVC structure with security, validation, and AJAX features required by the assignment.

---

## Technologies & Topics Used

The project combines front-end, back-end, database, and security practices taught in web technologies courses.

### Front-End


| Topic          | How it is used in this project                                                                                                             |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **HTML5**      | Semantic page structure, forms, tables, navigation, post cards, admin panels                                                               |
| **CSS3**       | Layout (Flexbox, CSS Grid), custom properties (`:root` variables), responsive design (`@media`), components (cards, badges, alerts, forms) |
| **JavaScript** | Client-side form validation, AJAX (`fetch`) for wishlist, search/filter, comments, admin actions without full page reload                  |


### Back-End


| Topic                  | How it is used in this project                                               |
| ---------------------- | ---------------------------------------------------------------------------- |
| **PHP**                | Server-side logic, session management, routing, controllers, models, views   |
| **MVC pattern**        | Separation into `controllers/`, `models/`, `views/`, `config/`               |
| **PDO (MySQL)**        | Database connection with **prepared statements** (SQL injection prevention)  |
| **Sessions & cookies** | Login state, roles, “Remember Me”, CSRF tokens                               |
| **File upload**        | Profile pictures and scout post images with server-side MIME and size checks |
| **Password security**  | `password_hash()` on register; `password_verify()` on login                  |


### Database


| Topic                | How it is used in this project                                              |
| -------------------- | --------------------------------------------------------------------------- |
| **MySQL**            | Relational database `travel_guide`                                          |
| **Tables**           | `users`, `posts`, `post_requests`, `wishlist`, `comments`, `cost_estimates` |
| **Keys & integrity** | Foreign keys, unique constraints (e.g. wishlist per user/post)              |


### Other Web Topics


| Topic               | How it is used in this project                         |
| ------------------- | ------------------------------------------------------ |
| **AJAX / JSON**     | API-style endpoints return JSON for dynamic UI updates |
| **XSS prevention**  | `htmlspecialchars()` when displaying user content      |
| **CSRF protection** | Hidden token on forms; verified on POST requests       |
| **Responsive UI**   | Mobile-friendly navigation and grids                   |
| **Apache (XAMPP)**  | Local hosting; `index.php` as front controller         |


---

## Default User Credentials

After importing `database.sql`, you can log in with these **demo accounts**:


| Role         | Display Name   | Email                     | Password      |
| ------------ | -------------- | ------------------------- | ------------- |
| Admin        | Site Admin     | `admin@travelguide.local` | `Admin@12345` |
| Scout        | Alex Scout     | `scout@travelguide.local` | `Admin@12345` |
| General User | Jamie Traveler | `user@travelguide.local`  | `Admin@12345` |


**Note:** All three accounts use the same password: `**Admin@12345`**

New users who register through the site start as **unverified** until an admin verifies them under **Admin → User Management**.

---

## How to Run the Project

1. Install **XAMPP** and start **Apache** and **MySQL**.
2. Copy the project folder to `htdocs` (e.g. `C:\xampp\htdocs\project1`).
3. Import `**database.sql`** in phpMyAdmin (creates database `travel_guide` and seed users).
4. Open: `**http://localhost/project1/index.php**`
5. Log in with any default email and password from the table above.

If the project folder name is not `project1`, update `BASE_URL` in `config/app.php`.

---

## Main Modules (Assignment Tasks)


| Task   | Module         | Main features                                                      |
| ------ | -------------- | ------------------------------------------------------------------ |
| Task 1 | Auth & profile | Register, login, remember me, profile, home page, wishlist (AJAX)  |
| Task 2 | Scout          | Post request CRUD, image upload, change requests                   |
| Task 3 | Admin          | Dashboard, user verify, post approve/reject, comment moderation    |
| Task 4 | User browse    | Browse posts, live search/filter (AJAX), comments, cost calculator |


---

## Project Folder Overview

```
config/         → App settings, database, routes
controllers/    → Page logic and JSON APIs
models/         → Database queries (PDO)
views/          → HTML/PHP templates
includes/       → Bootstrap, Auth, Security helpers
public/css/     → Stylesheets
public/js/      → Validation and AJAX scripts
public/uploads/ → Uploaded images
database.sql    → Database schema and seed data
index.php       → Application entry point
```

---

## Security Features (Summary)

- Prepared statements for all database queries  
- Hashed passwords (never stored as plain text)  
- CSRF tokens on form submissions  
- Escaped output to reduce XSS risk  
- Role-based access (admin / scout / user)  
- Validated file uploads (type and size)

---

*This README describes the project scenario, technologies used, and default login details for reviewers, instructors, and GitHub visitors.*