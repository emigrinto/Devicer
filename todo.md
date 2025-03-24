   # ElectronicsShop Coursework To-Do List
Target: Early May 2025 (40 days from March 22, 2025)

## Phase 1: Planning & Analysis (Mar 22–27)
- [x] **Draft Product Requirements Document (PRD)**
  - *When*: March 22–23, 2025
  - *How*: Outline technical specs for Devicer MVP: purpose (online electronics store), features (browse, filter, cart, checkout, admin panel), user roles (Guest, Registered User, Admin), tech stack (React, PHP, MySQL), UI (minimalistic blue/purple). Use survey for features.
  - *Tools*: Word
  - *Git*: In `docs` branch, add “prd.md”. Commit: “docs: add PRD draft”
     
    
- [x] **Draft Market Requirements Document (MRD)**
  - *When*: March 22–23, 2025 
  - *How*: Define market needs based on survey: target audience, key categories, customer priorities, competitors. Justify features.
  - *Tools*: Google Docs, Excel (survey).
  - *Git*: In `docs` branch, add “mrd.md”. Commit: “docs: add MRD draft”
     
    
- [x] **Finalize Project Scope**
  - *When*: March 23–24, 2025
  - *How*: Review survey, guidelines, PRD, and MRD. List features: Categories (Phones, Accessories, Laptops, Appliances, Apple), Filters (Quality, Price), Cart, Guest Checkout, Admin Add/Edit Products, 10% Discount Banner for Registered Users. 
  - *Tools*: Google Docs, Excel
  - *Git*: In `main` branch, add README.md with scope overview. Commit: “initial commit with project scope”
     
    
- [x] **Analyze Survey Data**
  - *When*: March 24–25, 2025
  - *How*: Use Excel to calculate % for categories, priorities, and prototype feedback. Note trends: discounts, detailed info, minimalism.
  - *Tools*: Excel
  - *Git*: In `docs` branch, add “survey_analysis.xlsx” and “survey_summary.md” with key findings. Commit: “docs: add survey analysis”
     
    
- [x] **Create Initial To-Do List Review**
  - *When*: March 25–27, 2025
  - *How*: Draft in Google Docs, then convert to Git.
  - *Tools*: Git, Google Docs
  - *Git*: In `docs` branch, add “todo.md” with this list. Commit: “docs: initial to-do list”





## Phase 2: Design & Modeling (Mar 28–Apr 5)
- [ ] **Develop Use Case Diagram**
  - *When*: March 28–29, 2025
  - *How*: Draw in Lucidchart with actors (Guest, Registered User, Admin) and actions (Browse, Filter, Add to Cart, Checkout, Manage Products). Export as PNG.
  - *Tools*: Lucidchart, Draw.io
  - *Git*: In `diagrams` branch, add “use_case_diagram.png”. Commit: “diagrams: add use case diagram”
     
    
- [ ] **Design ER Diagram**
  - *When*: March 30–April 1, 2025
  - *How*: Model entities (Products, Orders, Invoices, Clients, Admins, Categories) with relationships in MySQL Workbench. Define attributes. Add dependancies and restrictions. 
  - *Tools*: MySQL Workbench, Lucidchart, DB Designer
  - *Git*: In `diagrams` branch, add “er_diagram.png” (or .sql). Commit: “diagrams: add ER diagram”
     
    
- [ ] **Normalize Database**
  - *When*: April 2–3, 2025
  - *How*: Check ER diagram for 3NF: unique IDs, no redundancy (e.g., Products linked to Categories). 
  - *Tools*: MySQL Workbench, notes
  - *Git*: In `docs` branch, add “normalization.md” with process. Commit: “docs: database normalization notes”
     
    
- [ ] **Update Site Map**
  - *When*: April 4–5, 2025
  - *How*: Refine site map in LC based on survey: homepage, category pages (Phones, Laptops, etc.), product details, cart, checkout, admin panel. Keep minimalistic. Export as PNG.
  - *Tools*: Figma, Google Drawings
  - *Git*: In `diagrams` branch, add “site_map_v2.png”. Commit: “diagrams: update site map”




## Phase 3: Development Setup (Apr 6–10)
- [ ] **Set Up Development Environment**
  - *When*: April 6–7, 2025
  - *How*: On Ubuntu, run `sudo apt install php apache2 mysql-server`, install npm/Node.js for React. Test locally with `http://localhost`. Document steps.
  - *Tools*: Ubuntu Terminal, VS Code
  - *Git*: In `docs` branch, add “setup_guide.md” with commands. Commit: “docs: dev environment setup”
     
    
- [ ] **Initialize Project Structure**
  - *When*: April 8–9, 2025
  - *How*: Run `npx create-react-app frontend` for /frontend, create /backend with index.php, set up /database folder for SQL. Test basic file creation.
  - *Tools*: VS Code, Terminal
  - *Git*: In `dev` branch, push initial structure: /frontend, /backend, /database. Commit: “dev: init project structure”
     
    
- [ ] **Pre-Load Database with Generic Data**
  - *When*: April 9–10, 2025
  - *How*: Write SQL INSERTs in MySQL Workbench. Example: `INSERT INTO Products (ID, Name, Price, CategoryID) VALUES (1, 'iPhone 13', 800, 1);`. Test in MySQL.
  - *Tools*: MySQL Workbench
  - *Git*: In `database` branch, add “initial_data.sql”. Commit: “database: add initial data”




## Phase 4: Coding (Apr 11–25)
- [ ] **Build Frontend (React)**
  - *When*: April 11–17, 2025
  - *How*: Code React components (Home.js, ProductList.js, Cart.js, Login.js) in blue/purple/lilac minimalistic style. Fetch data via backend API calls. Test locally.
  - *Tools*: VS Code, React Dev Tools
  - *Git*: In `frontend` branch, commit daily (e.g., “frontend: add product list”). Merge to `dev` on April 17.
     
    
- [ ] **Develop Backend (PHP)**
  - *When*: April 18–22, 2025
  - *How*: Create PHP API endpoints (getProducts, addProduct, login, checkout) using PDO for MySQL. Handle guest sessions with PHP sessions. Test with Postman.
  - *Tools*: VS Code, Postman
  - *Git*: In `backend` branch, commit (e.g., “backend: add product API”). Merge to `dev` on April 22.
     
    
- [ ] **Implement Admin Panel**
  - *When*: April 23–25, 2025
  - *How*: Add React admin route (/admin), PHP endpoints (addProduct, updateProduct). Pre-authenticate admins via database. Test adding a product.
  - *Tools*: VS Code
  - *Git*: In `frontend` (“frontend: admin UI”) and `backend` (“backend: admin endpoints”) branches, commit. Merge to `dev` on April 25.
     
    
- [ ] **Add Mock Payment Demo**
  - *When*: April 25, 2025
  - *How*: Add a checkout button in React triggering “Payment Successful” alert (no real gateway). Test flow.
  - *Tools*: VS Code
  - *Git*: In `frontend` branch, commit (“frontend: mock payment”). Merge to `dev`.



## Phase 5: Testing & Documentation (Apr 26–May 1)
- [ ] **Test Application**
  - *When*: April 26–27, 2025
  - *How*: Manually test: browse, filter, cart, checkout (guest + registered), admin actions. Document results in Markdown.
  - *Tools*: Browser
  - *Git*: In `docs` branch, add “test_results.md”. Commit: “docs: testing outcomes”
     
      
- [ ] **Write Coursework Report**
  - *When*: April 28–30, 2025
  - *How*: Follow guidelines: Intro (goals), Main Body (survey, diagrams, code), Conclusion (results). Include screenshots. Draft in Google Docs.
  - *Tools*: Word
  - *Git*: In `docs` branch, add “report_draft.docx”. Commit: “docs: draft report”
     
    
- [ ] **Prepare Presentation**
  - *When*: May 1, 2025
  - *How*: Create 10–15 slides in Canva: scope, survey, design, demo, results. Plan live demo.
  - *Tools*: Canva
  - *Git*: In `docs` branch, add “defense.pptx”. Commit: “docs: defense slides”




## Phase 6: Finalization (May 2–5)
- [ ] **Polish Code & Docs**
  - *When*: May 2–3, 2025
  - *How*: Refactor React/PHP for clarity, proofread report. Test final build.
  - *Tools*: VS Code, Word
  - *Git*: In `frontend`, `backend`, `docs` branches, commit fixes (e.g., “fix: bugs”, “docs: final report”). Merge all to `main` on May 3.
     
    
- [ ] **Submit Pre-Defense**
  - *When*: May 4, 2025
  - *How*: Email/upload report to supervisor (check uni deadline, est. 7 days pre-defense).
  - *Tools*: Email, uni portal
  - *Git*: N/A (unless repo link required)
     
    
- [ ] **Optional Live Demo**
  - *When*: May 5, 2025
     




   
    
  - *Git*: In `docs` branch, add “deployment_guide.md”. Commit: “docs: live demo guide”
