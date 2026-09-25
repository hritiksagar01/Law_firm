# ⚖️ Law Firm Management — Monthly Hosting Cost Estimate

> **Application:** Stitch Legal Practice Management Platform  
> **Stack:** Laravel 13 · PHP 8.3 · FrankenPHP · Livewire · Tailwind CSS 4 · Alpine.js  
> **Database:** SQLite (dev) / PostgreSQL 16 or MySQL 8 (production)  
> **File Storage:** Amazon S3 (ap-south-1) for legal documents  
> **Currency:** All prices shown in **USD ($)** with **INR (₹)** equivalents at ~₹84/$1  
> **Date:** September 2026

---

## 📌 Executive Summary for Clients (In Simple Words)

### *"Why can't this be hosted on free/cheap $1 WordPress shared hosting?"*

Clients often ask why a custom platform needs a dedicated server (like AWS EC2 / Hetzner) instead of standard ₹99/month shared web hosting or free website builders. 

Here is the simple breakdown:

```
┌──────────────────────────────────────────┐      ┌──────────────────────────────────────────┐
│        Standard Static Website           │      │     Custom Legal Practice System         │
│         (Brochure / Portfolio)           │      │        (Enterprise SaaS Engine)          │
├──────────────────────────────────────────┤      ├──────────────────────────────────────────┤
│ 📄 Shows static text & images            │      │ ⚙️ Calculates billable hours & invoices │
│ 👤 Reads simple contact form             │      │ 🔒 Enforces strict lawyer/client privacy │
│ 🌐 No background jobs or security rules  │      │ 📑 Generates PDF contracts & documents   │
│ 💰 Costs ~₹0 - ₹100 / month              │      │ 🔔 Sends automated hearing reminders     │
│                                          │      │ 📊 Runs real-time audit logs & search    │
│                                          │      │ 💰 Requires ~$8 - $15 / month            │
└──────────────────────────────────────────┘      └──────────────────────────────────────────┘
```

### The 5 Reasons Dedicated Server Compute is Mandatory:

1. **⚙️ Real-Time Calculations & Business Logic:**  
   Unlike a simple website that just displays text, this platform runs complex legal logic — tracking case stages, LEDES billing codes, hourly rate calculations, trust accounting, and tax computations in real-time.

2. **🔒 Enterprise Legal Security & Data Privacy:**  
   Legal records require strict multi-tenant data separation. When an advocate or client logs in, the server evaluates strict role-based access rules (RBAC) to ensure unauthorized users can NEVER access confidential case documents or internal firm notes.

3. **📑 Secure Document Storage & PDF Processing:**  
   The system handles legal evidence, confidential contracts, and court filings. It securely streams files via encrypted AWS S3 pre-signed links and generates on-the-fly PDF invoices and opinion documents.

4. **🤖 Automated Background Queues & Notifications:**  
   The platform actively works even when nobody is logged in — tracking statutory court deadlines, queuing automated email/SMS reminders, and processing audit logs in background worker queues.

5. **🗄️ Relational Database Integrity:**  
   With 40 interconnected database models (Clients → Matters → Documents → Invoices → Audit Logs), a dedicated database process ensures zero data corruption, fast instant search, and complete audit history.

---

Based on analysis of the codebase:

| Resource | Details |
|:---------|:--------|
| **Models/Tables** | 40 Eloquent models across 31 migrations (firms, users, clients, matters, documents, invoices, time entries, tasks, events, messages, audit logs, RBAC, opinions, appointments, etc.) |
| **Controllers** | 27 controllers (16 app + 11 admin) |
| **Frontend** | Blade + Livewire + Alpine.js — server-rendered, no SPA overhead |
| **Queue** | Database queue driver (no Redis required for MVP) |
| **Cache** | Database cache driver |
| **File Uploads** | S3-backed document storage (legal documents, PDFs, evidence) |
| **Web Server** | FrankenPHP (Caddy-based, single binary, efficient) |
| **Real-time** | Laravel Reverb (self-hosted WebSockets) |

### Minimum Server Requirements (Production)

| Spec | Minimum | Recommended |
|:-----|:--------|:------------|
| **CPU** | 1 vCPU | 2 vCPU |
| **RAM** | 1 GB | 2 GB |
| **Disk** | 20 GB SSD | 40 GB SSD |
| **Bandwidth** | 500 GB/mo | 1 TB/mo |
| **PHP Version** | 8.3+ | 8.3+ |

> [!NOTE]
> Laravel + FrankenPHP is lightweight. A single `t3.micro` (1 vCPU, 1 GB RAM) **can** run this app for a small firm (< 20 concurrent users). However, 2 GB RAM is recommended for comfortable headroom with queue workers and WebSocket connections.

---

## 2. Provider-by-Provider Cost Breakdown

### 🟠 Option A — AWS (Amazon Web Services)

**Region:** `ap-south-1` (Mumbai)

| Service | Instance/Tier | Specs | Monthly Cost |
|:--------|:-------------|:------|:-------------|
| **EC2 (Compute)** | `t3.micro` | 2 vCPU (burstable), 1 GB RAM | **$8.18** (~₹687) |
| **EBS (Disk)** | `gp3` 30 GB | SSD storage for OS + app + SQLite/local DB | **$2.40** (~₹202) |
| **RDS PostgreSQL** | `db.t3.micro` | 1 vCPU, 1 GB RAM, 20 GB storage | **$14.26** (~₹1,198) |
| **S3 (Documents)** | Standard | 5 GB storage + 1,000 requests | **$0.15** (~₹13) |
| **Data Transfer** | Outbound | ~10 GB/mo | **$0.91** (~₹76) |
| **Elastic IP** | Static IP | 1 IP (free when attached to running instance) | **$0.00** |
| | | **AWS Total:** | **$25.90/mo (~₹2,176)** |

> [!TIP]
> **AWS Free Tier (First 12 months):** 750 hrs/mo of `t3.micro` + 750 hrs/mo of `db.t3.micro` + 5 GB S3 = effectively **₹0/mo for Year 1**. After that, costs jump to ~₹2,176/mo.

**💰 Option A1 — AWS Standard (Dedicated RDS Database):**

| Service | Instance/Tier | Specs | Monthly Cost |
|:--------|:-------------|:------|:-------------|
| **EC2 (Compute)** | `t3.micro` | 2 vCPU, 1 GB RAM | **$8.18** (~₹687) |
| **EBS (Disk)** | `gp3` 30 GB | SSD storage for OS + app | **$2.40** (~₹202) |
| **RDS PostgreSQL** | `db.t3.micro` | 1 vCPU, 1 GB RAM, 20 GB storage | **$14.26** (~₹1,198) |
| **S3 (Documents)** | Standard | 5 GB storage + requests | **$0.15** (~₹13) |
| **Data Transfer** | Outbound | ~10 GB/mo | **$0.91** (~₹76) |
| | | **AWS with RDS Total:** | **$25.90/mo (~₹2,176)** |

---

**💰 Option A2 — AWS Self-Hosted Database on EC2 (NO RDS):**

*(Running PostgreSQL or MySQL directly inside the EC2 machine alongside Laravel)*

| Setup Variant | EC2 Instance | Specs | Monthly Cost | Cost Savings vs RDS |
|:--------------|:-------------|:------|:-------------|:-------------------|
| **`t3.micro` (1 GB RAM)** | `t3.micro` + 30 GB EBS | 2 vCPU, 1 GB RAM | **$10.58/mo (~₹888)** | **Saves $14.26/mo (55% cheaper!)** |
| **`t3.small` (2 GB RAM)** ⭐ Recommended | `t3.small` + 30 GB EBS | 2 vCPU, 2 GB RAM | **$19.19/mo (~₹1,610)** | **Saves $5.65/mo while doubling RAM** |

> [!TIP]
> **Why self-hosting DB on EC2 is great for small firms:**
> 1. **Zero Database Network Latency:** App and PostgreSQL communicate over local socket (`localhost`), making queries faster than connecting to RDS over internal network.
> 2. **55% Cost Reduction:** Saves ~$14.26/mo by removing RDS.
> 3. **Easier Backups:** Automated daily backups can be done using simple `pg_dump` cron jobs to S3 ($0.05/mo) or EBS snapshots ($1.50/mo).

> [!CAUTION]
> **RAM Consideration for `t3.micro` (1 GB RAM):**  
> Running PHP-FPM / FrankenPHP + PostgreSQL + OS on 1 GB RAM can be tight. To avoid Out-Of-Memory (OOM) crashes:
> - Set up a **2 GB Swap File** on EBS (`sudo fallocate -l 2G /swapfile`).
> - Configure PostgreSQL `shared_buffers = 128MB` and `max_connections = 30`.
> - Alternatively, upgrade to **`t3.small` (2 GB RAM)** at ~$19/mo for complete peace of mind.

---

### 🔵 Option B — GCP (Google Cloud Platform)

**Region:** `asia-south1` (Mumbai)

| Service | Instance/Tier | Specs | Monthly Cost |
|:--------|:-------------|:------|:-------------|
| **Compute Engine** | `e2-micro` | 0.25 vCPU (shared), 1 GB RAM | **$7.34** (~₹616) |
| **Persistent Disk** | Standard 30 GB | HDD (SSD would be ~$5.10) | **$1.20** (~₹101) |
| **Cloud SQL PostgreSQL** | `db-f1-micro` | Shared vCPU, 0.6 GB RAM, 10 GB | **$9.37** (~₹787) |
| **Cloud Storage** | Standard | 5 GB docs | **$0.12** (~₹10) |
| **Network Egress** | Outbound | ~10 GB/mo | **$1.20** (~₹101) |
| | | **GCP Total:** | **$19.23/mo (~₹1,615)** |

> [!NOTE]
> GCP's `e2-micro` Free Tier is only available in US regions (`us-west1`, `us-east1`, `us-central1`). For `asia-south1` (Mumbai), you pay full price. Consider hosting in a US region if latency is acceptable.

**💰 GCP Free Tier Option (US Region):**

| Service | Instance/Tier | Monthly Cost |
|:--------|:-------------|:-------------|
| **Compute Engine** | `e2-micro` (Always Free, US region) | $0.00 |
| **Persistent Disk** | 30 GB Standard (Always Free) | $0.00 |
| **Cloud Storage** | 5 GB (Always Free) | $0.00 |
| **Cloud SQL** | ❌ No free tier — use SQLite | $0.00 |
| | **GCP Free Tier Total:** | **$0.00/mo (₹0)** |

> [!CAUTION]
> The GCP Always Free `e2-micro` has only 0.25 shared vCPU and 1 GB RAM. It **will** struggle under load. This is viable only for demo/staging, not production with real users. Also, US-region hosting means 150-200ms latency for Indian users.

---

### 🟣 Option C — Hostinger VPS

**Plan:** KVM 1 (cheapest KVM VPS)

| Service | Plan | Specs | Monthly Cost |
|:--------|:-----|:------|:-------------|
| **VPS KVM 1** | Promo (48-mo lock-in) | 1 vCPU, 4 GB RAM, 50 GB NVMe SSD, 4 TB BW | **$4.99–$6.49** (~₹420–₹545) |
| **Database** | Self-managed (MySQL/PostgreSQL on same server) | Included in VPS | **$0.00** |
| **S3 (Documents)** | Use existing AWS S3 bucket | 5 GB | **$0.15** |
| | | **Hostinger Total:** | **$5.14–$6.64/mo (~₹432–₹558)** |

> [!IMPORTANT]
> **Hostinger is self-managed.** You must install PHP 8.3, Composer, PostgreSQL/MySQL, configure Nginx/Caddy, set up SSL, manage security updates, and handle backups yourself. The promo price requires a **48-month commitment** (~₹20,160–₹26,784 upfront). Renewal rates are significantly higher (~$10–$15/mo).

**Hostinger Pros & Cons:**

| ✅ Pros | ❌ Cons |
|:--------|:--------|
| Best RAM-to-price ratio (4 GB!) | Self-managed (no DevOps support) |
| KVM virtualization (dedicated resources) | Promo requires 3–4 year lock-in |
| NVMe SSD storage | Renewal price much higher |
| Includes weekly backups | No managed database |
| DDoS protection | Limited datacenter locations |

---

### 🟢 Option D — Hetzner Cloud ⭐ RECOMMENDED

**Plan:** CX23 (cheapest current generation, Germany/Finland)

| Service | Plan | Specs | Monthly Cost |
|:--------|:-----|:------|:-------------|
| **Cloud Server CX23** | Shared vCPU | 2 vCPU, 4 GB RAM, 40 GB SSD, 20 TB BW | **$6.49/€5.49** (~₹545) |
| **Database** | Self-managed (PostgreSQL on same server) | Included | **$0.00** |
| **S3 (Documents)** | Use AWS S3 or Hetzner Object Storage | 5 GB | **$0.15** |
| **Snapshot Backups** | Optional | 20% of server cost | **$1.30** (~₹109) |
| | | **Hetzner Total:** | **$7.94/mo (~₹667)** |

> [!TIP]
> **Save €0.50/mo** by choosing IPv6-only configuration. Hetzner also has **hourly billing** with a monthly cap — you only pay for what you use, capped at the monthly price.

**Why Hetzner is the best value:**

| Metric | Hetzner CX23 | AWS t3.micro | GCP e2-micro |
|:-------|:-------------|:-------------|:-------------|
| **vCPU** | 2 | 2 (burst) | 0.25 (shared) |
| **RAM** | 4 GB | 1 GB | 1 GB |
| **Storage** | 40 GB SSD | 30 GB EBS ($2.40) | 30 GB HDD ($1.20) |
| **Bandwidth** | 20 TB | Pay per GB | Pay per GB |
| **Monthly Cost** | **$6.49** | **$8.18** (compute only) | **$7.34** (compute only) |
| **All-in Cost** | **~$8/mo** | **~$26/mo** (with RDS) | **~$19/mo** (with Cloud SQL) |

---

### 🔴 Option E — DigitalOcean

**Plan:** Basic Droplet

| Service | Plan | Specs | Monthly Cost |
|:--------|:-----|:------|:-------------|
| **Droplet (1 GB)** | Basic | 1 vCPU, 1 GB RAM, 25 GB SSD, 1 TB BW | **$6.00** (~₹504) |
| **Droplet (2 GB)** | Basic | 1 vCPU, 2 GB RAM, 50 GB SSD, 2 TB BW | **$12.00** (~₹1,008) |
| **Managed PostgreSQL** | Basic | 1 vCPU, 1 GB RAM, 10 GB SSD | **$15.00** (~₹1,260) |
| **S3-compatible Spaces** | Included | 250 GB + 1 TB BW | **$5.00** (~₹420) |
| | | **DO (self-managed DB):** | **$6.00–$12.00/mo** |
| | | **DO (managed DB):** | **$21.00–$27.00/mo** |

---

### 🟡 Option F — Railway / Render (PaaS)

| Service | Railway | Render |
|:--------|:--------|:-------|
| **Free Tier** | 30-day trial + $1/mo credit | 750 hrs (spins down after 15 min inactivity) |
| **Hobby/Starter** | ~$5/mo (usage-based) | $7/mo (always-on) |
| **Database** | PostgreSQL $5–$10/mo | PostgreSQL $7/mo (free expires in 30 days) |
| **Total (Paid)** | **$10–$15/mo (~₹840–₹1,260)** | **$14–$21/mo (~₹1,176–₹1,764)** |
| **Docker Support** | ✅ Nixpacks + Docker | ✅ Docker |
| **Git Deploy** | ✅ Automatic | ✅ Automatic |
| **Laravel Support** | ✅ Good | ✅ Good (render.yaml exists in project) |

> [!NOTE]
> Your project already has a [render.yaml](file:///D:/Coding/Lawyer%20Management/render.yaml) configured for Render with a free plan. However, Render's free database expires after 30 days and the free web service has cold-start delays.

---

## 3. Ancillary Service Costs (All Providers)

These costs apply regardless of which compute provider you choose:

| Service | Provider | Free Tier | Cost After Free |
|:--------|:---------|:----------|:----------------|
| **S3 Document Storage** | AWS S3 (already configured) | 5 GB (Free Tier Year 1) | $0.023/GB/mo |
| **Email (Transactional)** | Brevo (Sendinblue) | ✅ 300 emails/day free | $0/mo |
| **Domain Name** | Any registrar | — | ~₹800/year (~₹67/mo) |
| **SSL Certificate** | Let's Encrypt | ✅ Free forever | $0/mo |
| **DNS + CDN** | Cloudflare | ✅ Free plan | $0/mo |
| **WebSockets** | Laravel Reverb (self-hosted) | ✅ Free | $0/mo |
| | | **Ancillary Total:** | **~₹67/mo** (domain only) |

---

## 4. Comparison Summary — All Providers

| Provider | Plan | vCPU | RAM | Disk | DB Included? | Total Monthly Cost | INR/mo |
|:---------|:-----|:-----|:----|:-----|:-------------|:-------------------|:-------|
| **GCP Free Tier** | `e2-micro` (US) | 0.25 | 1 GB | 30 GB | SQLite only | **$0** | **₹0** |
| **AWS Free Tier** | `t3.micro` + RDS (Year 1) | 2 (burst) | 1 GB | 30 GB | ✅ RDS | **$0** | **₹0** |
| **Hostinger KVM 1** | Promo (48-mo) | 1 | 4 GB | 50 GB | Self-managed | **~$5** | **~₹420** |
| ⭐ **Hetzner CX23** | Monthly | 2 | 4 GB | 40 GB | Self-managed | **~$8** | **~₹670** |
| **DigitalOcean** | Basic Droplet | 1 | 1 GB | 25 GB | Self-managed | **$6** | **₹504** |
| **Railway** | Hobby + DB | Shared | 512 MB | 1 GB | ✅ Managed | **~$10** | **~₹840** |
| **AWS (Post Free Tier)** | `t3.micro` + RDS | 2 (burst) | 1 GB | 30 GB | ✅ RDS | **~$26** | **~₹2,176** |
| **Render** | Starter + DB | Shared | 512 MB | 1 GB | ✅ Managed | **~$14** | **~₹1,176** |
| **GCP Mumbai** | `e2-micro` + Cloud SQL | 0.25 | 1 GB | 30 GB | ✅ Managed | **~$19** | **~₹1,615** |

---

## 5. Storage Estimate for Database

Estimated database growth based on the 40-model schema:

| Data Type | Rows/Year (Small Firm) | Rows/Year (Medium Firm) | Size/Row | Annual Size |
|:----------|:----------------------|:------------------------|:---------|:------------|
| **Users** | 10–30 | 50–200 | ~1 KB | < 1 MB |
| **Clients** | 50–200 | 500–2,000 | ~2 KB | < 4 MB |
| **Matters/Cases** | 100–500 | 1,000–5,000 | ~1 KB | < 5 MB |
| **Documents (metadata)** | 500–2,000 | 5,000–20,000 | ~0.5 KB | < 10 MB |
| **Time Entries** | 2,000–10,000 | 20,000–100,000 | ~0.3 KB | < 30 MB |
| **Invoices** | 200–1,000 | 2,000–10,000 | ~0.5 KB | < 5 MB |
| **Messages** | 5,000–20,000 | 50,000–200,000 | ~0.5 KB | < 100 MB |
| **Audit Logs** | 10,000–50,000 | 100,000–500,000 | ~0.3 KB | < 150 MB |
| **Events/Tasks/Notes** | 1,000–5,000 | 10,000–50,000 | ~0.3 KB | < 15 MB |
| | | | **Year 1 Total:** | **~100 MB – 1 GB** |
| | | | **Year 3 Total:** | **~500 MB – 3 GB** |

> [!TIP]
> Database size is NOT a concern. Even a `db.t3.micro` with 20 GB is **20× more** than what a small-to-medium firm needs in 3 years. The actual storage cost driver is **document files** stored in S3, not the database.

### Document File Storage (S3)

| Usage Level | Documents/Year | Avg Size | Annual Storage | S3 Cost/Year |
|:------------|:---------------|:---------|:---------------|:-------------|
| **Light** (solo practitioner) | 500 | 2 MB | ~1 GB | < $0.30 |
| **Medium** (5-person firm) | 5,000 | 2 MB | ~10 GB | < $2.80 |
| **Heavy** (20-person firm) | 25,000 | 3 MB | ~75 GB | < $21.00 |

---

## 6. Recommended Setup — By Budget Tier

### 🏷️ Tier 1 — Absolute Minimum (~₹0–₹500/mo)

> For solo practitioners, demos, or proof-of-concept

```
┌─────────────────────────────────────────────────┐
│  AWS Free Tier (Year 1) OR GCP Always Free      │
│  ├─ t3.micro / e2-micro                         │
│  ├─ SQLite database (on local disk)              │
│  ├─ AWS S3 free tier (5 GB documents)            │
│  ├─ Brevo free (300 emails/day)                  │
│  └─ Let's Encrypt SSL                            │
│                                                   │
│  Monthly: $0–$5 (₹0–₹420)                        │
└─────────────────────────────────────────────────┘
```

### 🥈 Tier 2 — Best Value for Small Firm (~₹500–₹800/mo) ⭐ RECOMMENDED

> For 1–10 user firms, production-ready

```
┌─────────────────────────────────────────────────┐
│  Hetzner CX23 (Germany/Finland)                  │
│  ├─ 2 vCPU, 4 GB RAM, 40 GB SSD                 │
│  ├─ PostgreSQL 16 (self-hosted on same server)   │
│  ├─ FrankenPHP + Laravel Reverb                  │
│  ├─ AWS S3 (existing bucket: lawfirm01)          │
│  ├─ Brevo free (emails)                          │
│  ├─ Cloudflare free (DNS + CDN + SSL)            │
│  └─ Weekly snapshots ($1.30)                     │
│                                                   │
│  Monthly: ~$8 (~₹670)                             │
└─────────────────────────────────────────────────┘
```

### 🥇 Tier 3 — Production-Grade for Growing Firm (~₹2,000–₹3,500/mo)

> For 10–50 users, managed services, high availability

```
┌─────────────────────────────────────────────────┐
│  AWS t3.small ($16.79/mo)                        │
│  ├─ 2 vCPU, 2 GB RAM                            │
│  ├─ 40 GB gp3 EBS ($3.20)                       │
│  │                                               │
│  AWS RDS PostgreSQL db.t3.micro ($14.26/mo)      │
│  ├─ Automated backups                            │
│  ├─ Multi-AZ failover (optional, +$14)           │
│  │                                               │
│  AWS S3 ($0.50/mo at 20 GB)                      │
│  AWS CloudFront CDN (1 TB free)                  │
│  └─ Amazon SES ($0.10/1000 emails)               │
│                                                   │
│  Monthly: $35–$50 (~₹2,940–₹4,200)               │
└─────────────────────────────────────────────────┘
```

---

## 7. Key Recommendations

1. **Start with Hetzner CX23** (~₹670/mo) — best value, 4 GB RAM comfortably runs Laravel + PostgreSQL + queue workers + Reverb WebSockets on a single server.

2. **Use AWS Free Tier for Year 1** if the client is new to AWS — it's literally free for 12 months, giving time to validate the product.

3. **Keep documents in S3** (`lawfirm01` bucket already configured) — decouple file storage from compute so you can switch servers without data loss.

4. **Don't over-provision** — this application with < 50 users does NOT need managed Kubernetes, load balancers, or multi-region setups. A single VPS handles this comfortably.

5. **Budget for domain + SSL** — ~₹800/year for domain, SSL is free via Let's Encrypt or Cloudflare.

6. **Set billing alerts** — On AWS/GCP, always configure budget alerts to avoid surprise charges.

---

## 8. Cost Projection — 3 Year Total Cost of Ownership

| Provider | Year 1 | Year 2 | Year 3 | **3-Year Total** |
|:---------|:-------|:-------|:-------|:-----------------|
| **AWS Free Tier → t3.micro + RDS** | ₹0 | ₹26,112 | ₹26,112 | **₹52,224** |
| **Hetzner CX23** | ₹8,040 | ₹8,040 | ₹8,040 | **₹24,120** |
| **Hostinger KVM 1** (promo) | ₹5,040 | ₹5,040 | ₹5,040 | **₹15,120** *(upfront)* |
| **DigitalOcean** (1 GB droplet) | ₹6,048 | ₹6,048 | ₹6,048 | **₹18,144** |
| **GCP e2-micro (Mumbai)** + Cloud SQL | ₹19,380 | ₹19,380 | ₹19,380 | **₹58,140** |

> [!IMPORTANT]
> Hostinger appears cheapest on paper, but requires upfront payment for 3–4 years and the renewal rate is much higher. Hetzner offers the best **no-commitment monthly billing** value. AWS Free Tier is ideal for Year 1 validation before committing to a provider.

---

*Generated on September 25, 2026 — prices sourced from official provider pricing pages and may vary. Always verify with the provider's pricing calculator before committing.*
