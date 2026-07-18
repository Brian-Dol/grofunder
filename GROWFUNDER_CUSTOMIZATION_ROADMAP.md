# Growfunder Customization Roadmap

## Current System vs Growfunder Requirements

### ✅ What's Already Built (Use As-Is)

| Feature | Status | Notes |
|---------|--------|-------|
| Loan Management | ✅ Full | Application → Approval → Disbursement → Repayment |
| Admin Dashboard | ✅ Full | View all loans, borrowers, transactions |
| Role-Based Access | ✅ Full | SuperAdmin, Admin, Agent roles built-in |
| Repayment Tracking | ✅ Full | Track payments, issue reminders |
| Financial Reports | ✅ Full | P&L, Cash Flow, Balance Sheet |
| Double-Entry Accounting | ✅ Full | Chart of Accounts, Journal Entries |
| Email Notifications | ✅ Full | Loan approvals, repayment reminders |
| PDF Export | ✅ Full | Export loans, statements, agreements |
| User Management | ✅ Full | Create users, assign roles, permissions |

---

### ⚠️ Needs Customization (Growfunder-Specific)

#### 1. **Stakeholder Model** (CRITICAL)
**Current:** Lender → Borrower  
**Needed:** Growfunder Admin → Agent → Farmer

```
Current Architecture:
├── SuperAdmin (platform)
├── Admin (company)
└── Borrower (individual)

Growfunder Architecture:
├── SuperAdmin (Growfunder founders)
├── Growfunder Admin (Kelvin/Melanie - view all data)
├── Agent/Cooperative (represent farmers)
│   └── Farmers (get loans through agent)
└── Investor (view loan portfolio)
```

**Work Required:** ~5 hours
- Create "Cooperative" model
- Create "Agent" user role linked to Cooperative
- Add "Investor" user role with dashboard view
- Update permissions matrix in FilamentShield

---

#### 2. **Farmer Identification** (CRITICAL)
**Current:** User ID or email  
**Needed:** Mobile number as primary ID

```
Current: 
- User ID: 1, 2, 3...
- Email: farmer@example.com

Growfunder:
- Mobile: +256701234567 (primary identifier)
- Name: John Farmer
- Cooperative: Kampala Farmers Coop
```

**Work Required:** ~3 hours
- Create migration to add `mobile_number` to farmers
- Make mobile number unique constraint
- Update farmer search/lookup to use mobile
- Add mobile number validation (E.164 format)

---

#### 3. **Payment Routing** (HIGH)
**Current:** Internal wallet transfers  
**Needed:** Farmers pay via mobile money to Growfunder account

```
Current Flow:
Admin adds money to wallet → Admin approves loan → Funds transfer internally

Growfunder Flow:
Loan approved → Farmer receives SMS with payment instructions
Farmer sends payment via M-Pesa/MTN/Airtel → Growfunder receives payment
→ System verifies payment via API → Updates loan status
```

**Work Required:** ~8 hours
- Integrate M-Pesa/MTN API (depending on region)
- Build payment gateway wrapper
- Create webhook handlers for payment callbacks
- Add payment verification logic
- Update loan workflow to include "awaiting_payment" status

---

#### 4. **Data Aggregation** (HIGH - Your Competitive Advantage)
**Current:** Single system source  
**Needed:** Pull data from multiple cooperatives' legacy systems

```
Current:
Growfunder System → Database → Dashboard

Growfunder (Multi-Source):
Legacy System 1 (Cooperative A) ┐
Legacy System 2 (Cooperative B) ├→ Data Sync API → Normalization → 
Legacy System 3 (Cooperative C) ┘   Dashboard

```

**Work Required:** ~15 hours
- Build data sync API layer
- Create ETL (Extract, Transform, Load) jobs
- Data normalization rules (date formats, field mapping)
- Duplicate detection and merging
- Scheduled sync tasks (cron jobs)
- Data quality dashboard

---

#### 5. **Investor Dashboard** (MEDIUM)
**Current:** Not built  
**Needed:** Investor view of portfolio performance

```
Investor sees:
- Total invested amount
- Active loans (by farmer, cooperative, status)
- Repayment status (on-time, late, defaulted)
- Expected returns
- ROI calculations
- Summary by cooperative
```

**Work Required:** ~6 hours
- Create new Filament Resource for Investor panel
- Build query/report for investor-specific data
- Create visualizations (charts, metrics)
- Add permission gating (investors only see assigned loans)

---

#### 6. **Multi-Language Support** (LOW)
**Current:** English only  
**Needed:** Maybe Luganda/local languages for farmers

**Work Required:** ~4 hours (if needed)
- Setup Laravel localization
- Translate dashboard and farmer forms
- Add language switcher

---

## Implementation Priority

### MVP (Weeks 1-2)
1. **Farmer Identification** - Mobile as primary ID ⏱️ 3h
2. **Stakeholder Model** - Add Cooperative + Agent/Investor roles ⏱️ 5h
3. **Core Dashboard Customization** - Growfunder admin view ⏱️ 4h
4. **Testing** - Verify all core features work with new model ⏱️ 4h
5. **Local Deployment** - Get running on Brian's machine ⏱️ 2h

**Subtotal MVP: ~18 hours**

### Phase 1.5 (Weeks 2-3) - Before Production
6. **Payment Gateway Integration** - M-Pesa/mobile money ⏱️ 8h
7. **Investor Dashboard** - Basic version ⏱️ 6h
8. **Cloud Deployment** - Setup production server ⏱️ 4h

**Subtotal Phase 1.5: ~18 hours**

### Phase 2 (Future)
9. **Data Aggregation** - Pull from legacy systems ⏱️ 15h
10. **Mobile App** - Farmer-facing application ⏱️ 40h+
11. **Advanced Analytics** - Credit scoring, KPI dashboards ⏱️ 20h+

---

## File Changes Summary

### To Create (NEW)
- `app/Models/Cooperative.php`
- `app/Filament/Resources/CooperativeResource.php`
- `app/Filament/Resources/InvestorResource.php` (read-only)
- `app/Filament/Pages/InvestorDashboard.php`
- `database/migrations/xxxx_xx_xx_create_cooperatives_table.php`
- `database/migrations/xxxx_xx_xx_add_mobile_to_farmers.php`
- `app/Services/PaymentGatewayService.php` (for Phase 1.5)

### To Modify (EXISTING)
- `app/Models/User.php` - Add investor role, cooperative_id
- `app/Models/Borrower.php` - Rename to Farmer, add mobile_number, cooperative_id
- `database/seeders/ShieldSeeder.php` - Add investor role + permissions
- `routes/web.php` - Add investor dashboard route
- `config/auth.php` - Add investor role type

### Import from Existing
- Use existing `LoanResource` (agent creates loans)
- Use existing `UserResource` (manage agents/admins/investors)
- Use existing `LoanApplicationResource` (track applications)
- Use existing reporting (already has repayment tracking)

---

## Testing Checklist

- [ ] Super Admin can create Cooperatives
- [ ] Super Admin can create Agent users linked to Cooperatives
- [ ] Agents can create Farmers with mobile number
- [ ] Agents can create Loan applications for their Farmers
- [ ] Admin sees aggregated data from all cooperatives
- [ ] Investor can see assigned loans (read-only)
- [ ] Farmer can be uniquely identified by mobile number
- [ ] All existing loan workflows still work
- [ ] Reports generate correctly with new model
- [ ] Permissions correctly restrict access by role

---

## Deployment Checklist (Before Production)

- [ ] Database backed up to PostgreSQL
- [ ] Environment secrets stored in `.env` (not committed)
- [ ] SSL certificate configured
- [ ] Payment gateway API keys configured
- [ ] Email service configured (SendGrid, AWS SES, etc.)
- [ ] Database migrations tested on production
- [ ] Backup strategy established
- [ ] Monitoring/logging configured
- [ ] Load testing done (expected 200 farmers = minimal)
- [ ] User acceptance testing with real agents

---

## Git Workflow (if using version control)

```powershell
# Create feature branches for each customization
git checkout -b feature/farmer-identification
git checkout -b feature/cooperative-model
git checkout -b feature/investor-dashboard

# Make commits
git add .
git commit -m "Add mobile number to farmer model"

# Push to repository
git push origin feature/farmer-identification

# Merge to main after testing
git checkout main
git merge feature/farmer-identification
```

---

## Questions to Answer Before Building

1. **Mobile Money Provider?** M-Pesa? MTN? Airtel?
2. **Region/Country?** (Uganda?) → Affects mobile number format
3. **Investor Model?** How do investors fund loans? Lump sum? Per-loan?
4. **Timeline?** How soon do you need payment integration?
5. **Legacy Data?** How many cooperatives? What systems do they use?
6. **User Count?** Estimated agents? Farmers? Investors?

---

## Quick Reference: What Each Queue Does

| View | Who? | What? |
|------|------|-------|
| **Filament Admin** | Superadmin, Growfunder Admin | Everything: users, loans, accounting, reports |
| **Agent Portal** | Cooperative agents | Their farmers, their loan applications, their repayments |
| **Investor Dashboard** | Investors | Their loans, performance, ROI |
| **Farmer Portal** | (To build) | Apply for loan, track repayment, make payments |
