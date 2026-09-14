# 🎨 UI/UX Design Prompt — Legal Practice Management Platform
## For Google Stitch / Figma AI / Any Design Tool

---

## COPY THIS ENTIRE PROMPT INTO GOOGLE STITCH:

---

Design a comprehensive, premium, modern UI/UX for a **Legal Practice Management SaaS Platform** — a web application used by law firms to manage clients, cases (called "matters"), legal documents, communication, tasks, billing, and calendars.

### DESIGN DIRECTION

**Aesthetic:** Premium, clean, minimal legal SaaS. Think Notion meets Clio meets Linear — not corporate or stuffy, but sophisticated and trustworthy. The app should feel like a tool built by designers, not developers.

**Color Palette:**
- Background: Warm beige/cream tones (`#FBFAF7` base, `#F3EFE6` secondary, `#EDE8DD` tertiary)
- Primary Action: Deep forest green (`#1A3C2A` buttons, `#2D5A3D` hover)
- Sidebar: Dark charcoal-green (`#1A1D1A` or `#111714`)
- Text: Near-black (`#1C1C1C` headings, `#4A4A4A` body)
- Accent: Warm gold (`#C9A84C`) for premium highlights, badges
- Status Colors: Green (#10B981) success, Blue (#3B82F6) info, Amber (#F59E0B) warning, Red (#EF4444) danger
- Cards/Panels: White (`#FFFFFF`) with subtle `0.5px` borders and soft shadows

**Typography:**
- Brand/Logo: Serif font (Playfair Display or Cormorant Garamond) — conveys trust and tradition
- Headings: Semi-bold sans-serif (Inter or DM Sans) — clean and modern
- Body: Regular weight sans-serif (Inter 400/500) — highly legible
- Monospace: JetBrains Mono — for reference numbers, case IDs

**Design Tokens:**
- Border radius: 8px (cards), 6px (inputs), 20px (badges/pills)
- Shadow: `0 1px 3px rgba(0,0,0,0.06)` (cards), `0 4px 12px rgba(0,0,0,0.08)` (modals)
- Spacing scale: 4px base unit (4, 8, 12, 16, 24, 32, 48, 64)
- Transitions: 150ms ease-in-out for all interactive elements

---

### PAGES TO DESIGN (design ALL of these as complete, pixel-perfect screens):

#### 1. LOGIN PAGE
- Centered card on beige background
- Logo "Quire" (or your brand name) in serif font at top
- Tagline underneath: "matters · documents · billing" in small caps, letter-spaced
- Email input field
- Password input field with show/hide toggle
- "Forgot password?" link aligned right
- Large "Sign in" button in forest green
- Footer: Three trust badges with icons — "Private file storage", "Two-step sign-in", "Every access is logged"
- Clean, airy layout with generous whitespace

#### 2. MAIN DASHBOARD (Attorney/Firm Admin View)
- **Left Sidebar** (dark, 260px wide):
  - Logo at top
  - Navigation items with icons: Dashboard, Matters, Clients, Documents, Messages (with unread badge), Tasks, Calendar, Billing
  - Divider
  - Firm Settings, Account
  - User avatar + name at bottom with sign-out
- **Top Header Bar**:
  - Global search input ("Search matters, clients, documents…") with ⌘K shortcut hint
  - Notification bell icon with red dot indicator
  - User avatar small
- **Main Content Area**:
  - Page title "Good afternoon, Margaret" with today's date
  - **KPI Cards Row** (4 cards): Open Matters (count + trend arrow), Tasks Due This Week, Uploads to Review, Outstanding Balance ($)
  - **Two-Column Layout Below**:
    - Left (60%): "Your tasks" checklist (checkbox + task name + priority pill + matter link + due date), "Waiting on you" section with client avatars and preview text
    - Right (40%): "Next two weeks" mini calendar summary, "Recent activity" feed with user avatars and timestamps

#### 3. MATTERS LIST PAGE
- Page header: "Matters" title + "New matter" green button
- Filter bar: Practice area dropdown, Status pills (Active, Discovery, Negotiation, Closed), Search input
- Matter cards/table showing: Reference # (monospace), Title, Client name, Practice area badge, Status badge, Lead attorney avatar, Last activity date
- Each row clickable to detail page

#### 4. MATTER DETAIL PAGE (Most Complex — 9 Tabs)
- **Header**: Matter title "Marsh v. Castellan Property Group", Reference "HO-2026-0042", Status badge, Practice area badge
- **Sub-header**: Client name (linked), Lead attorney, Date opened, Next court date
- **Tab Navigation** (horizontal): Overview, Work Product, Internal Notes, Client Files, Requests, Messages, Tasks, Timeline, Billing
- **Overview Tab Content**:
  - Case summary card (description, key dates, court info)
  - Assigned team members (avatar row with role labels)
  - Quick stats (documents count, pending tasks, unread messages)
  - Recent activity timeline

#### 5. DOCUMENTS PAGE
- Page header with "Upload" button
- Category filter tabs: All, Engagement, Pleadings, Court Orders, Correspondence, Work Product, Discovery, Evidence, Contracts
- Document table: Icon (by file type), Name, Category badge, Matter link, Uploaded by (avatar), Date, Privilege indicator (🔒), Actions dropdown
- Upload modal with drag-and-drop zone and camera capture button

#### 6. MESSAGES PAGE
- Split layout:
  - Left panel (35%): Thread list with unread indicators, thread type badge (Internal 🔒 / Client 👤), last message preview, timestamp
  - Right panel (65%): Active thread with message bubbles (left-aligned for others, right-aligned for self), sender avatar, timestamp, attachment chips, text input with send button and attach icon

#### 7. CALENDAR PAGE
- Month view (FullCalendar-style) with color-coded event dots
- View toggle: Month / Week / Day / Agenda
- Events show: title, time, matter reference
- Client visibility badge on events ("Client can see" / "Firm only")
- Click to create event modal

#### 8. BILLING PAGE
- **Summary Cards**: Total Outstanding, Overdue Amount, Collected This Month, Trust Balance
- Invoice table: Invoice #, Client, Matter, Amount, Status badge (Draft/Sent/Paid/Overdue), Due date, Actions
- "Create Invoice" button

#### 9. CLIENT PORTAL — Dashboard (Separate, Simpler Design)
- Simplified top navigation bar (no sidebar — horizontal nav)
- Welcome greeting with client name
- **Action Required** section highlighted in amber: Pending document requests, Unread messages
- **My Matters** cards with case stage tracker (visual progress bar — "Stage 3 of 7: Discovery")
- **Recent Documents** shared by legal team
- **Upcoming Events** with date and description

#### 10. CLIENT PORTAL — Document Request Upload
- Request title and description from attorney
- Due date highlighted
- Large upload zone with "Choose file" and "Use camera" buttons
- Camera capture interface (live viewfinder, capture button, retake/confirm)
- Preview of captured/selected file before submission

#### 11. CLIENT PORTAL — Invoice Payment
- Invoice details: line items table, subtotal, tax, total
- Payment method selector: Credit card form, Bank wire transfer details
- "Pay now" button

#### 12. SUPER ADMIN — Platform Dashboard
- Dark-themed or distinct from firm workspace
- Platform-wide KPIs: Total Firms, Total Users, Active Matters, Revenue
- Firms table with: Name, Seats Used/Allocated, Subscription Plan, Status, Revenue
- Sign-in history log
- System settings link

#### 13. SETTINGS PAGES
- **Firm Profile**: Logo upload, name, address, timezone, currency, date format
- **Team Management**: Member list with avatar, name, email, role badge, status, last active, "Invite" button
- **Roles & Permissions**: Interactive checkbox matrix (rows = permissions grouped by module, columns = roles)
- **Document Categories**: List with color dots, drag-to-reorder, add/edit/delete
- **Audit Log**: Filterable table (user, action, entity, timestamp, IP)

#### 14. MOBILE RESPONSIVE VERSIONS
- Design mobile versions (375px width) for:
  - Login page
  - Dashboard (stacked single-column)
  - Matter list (card-based, not table)
  - Document upload with camera capture prominently featured
  - Messages (full-screen thread view)
  - Client portal dashboard

---

### DESIGN PRINCIPLES TO FOLLOW

1. **Information Density**: Legal professionals work with lots of data — show dense but organized information. Don't hide things behind too many clicks.
2. **Visual Hierarchy**: Use font weight, size, and color to create clear scanning patterns. Important numbers should be large and bold.
3. **Status Communication**: Every item should clearly show its state — use color-coded badges/pills for statuses (green=active, blue=discovery, amber=pending, red=overdue, gray=closed).
4. **Trust & Security**: Visual cues that reinforce security — lock icons, "Privileged" badges, visibility indicators ("Firm only" / "Client can see").
5. **Whitespace**: Generous padding inside cards and between sections. Don't cram elements together.
6. **Micro-interactions**: Hover states on cards (subtle lift shadow), button press states, smooth transitions, loading skeletons.
7. **Empty States**: Design empty states with illustrations and helpful CTAs ("No matters yet. Create your first matter →").
8. **Accessibility**: WCAG AA contrast ratios, focus ring indicators, proper label associations.

### COMPONENT LIBRARY TO INCLUDE

Design these as reusable components:
- Buttons (Primary, Secondary, Danger, Ghost, Icon-only) in 3 sizes (sm, md, lg)
- Input fields (text, email, password with toggle, textarea, select/dropdown, date picker, file upload)
- Badges/Pills (status colors, priority levels, role indicators)
- Cards (content card, stat card, user card, matter card)
- Tables (with sorting indicators, action dropdowns, pagination)
- Modals (small confirmation, medium form, large content)
- Sidebar navigation (with active state, badge counts, collapsed mode)
- Toast notifications (success, error, warning, info)
- Tabs (horizontal, with counts)
- Avatar (single, group/stack, with status dot)
- Timeline/Activity feed item
- Search (inline with dropdown results, full-page results)
- Calendar event blocks (color-coded)
- Progress bar / Stage tracker
- Skeleton loading states

---

### OUTPUT FORMAT

Generate complete, high-fidelity designs for all 14 pages listed above, plus the component library. Each page should be production-ready — not a wireframe. Use the exact colors, typography, and spacing specified. Include both desktop (1440px) and mobile (375px) versions where specified.

---
