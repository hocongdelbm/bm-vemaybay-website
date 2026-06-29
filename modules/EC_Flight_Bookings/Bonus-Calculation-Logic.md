# Bonus Calculation Logic - Booker & Operations Team

## 1. Constants & Variables
- `MIN_SERVICE_FEE`: 110,000 VND (Minimum fee to qualify for bonus) [1].
- `THRESHOLD_FEE`: 120,000 VND (Base for calculating additional surplus bonus) [1].
- `OA_MULTIPLIER`: 
    - `1.0` if "Entered OA" (Đã vào OA) [1].
    - `0.5` if "Not Entered OA" [1].
- `P_dv`: Average Service Fee per ticket (Actual fee collected from customer) [1].
- `SURPLUS`: `max(0, P_dv - THRESHOLD_FEE)` [1].

---

## 2. Domestic Ticket Bonus Formulas (Per Ticket)
Total Bonus (`T`) for a booking is calculated based on customer source:

| Customer Category | Formula for Total Bonus (T) |
| :--- | :--- |
| **1. System/Company Source** | `T = [5,500 + (10% * SURPLUS)] * OA_MULTIPLIER` |
| **2. Reference/Customer Service** | `T = [22,000 + (30% * SURPLUS)] * OA_MULTIPLIER` |
| **3. Personal Effort Source** | `T = [33,000 + (40% * SURPLUS)] * OA_MULTIPLIER` |

---

## 3. International Ticket Bonus (Fixed Per Ticket)
*International bonuses are fixed and do not depend on the service fee surplus logic.*

- **Southeast Asia:** 300,000 VND/ticket [1].
- **Other Asia:** 350,000 VND/ticket [1].
- **Other Regions:** 400,000 VND/ticket [1].

---

## 4. Bonus Allocation (70/30 Split)
The Total Bonus (`T`) is split into Direct and Indirect funds:

1. **Direct Bonus (70%):** Awarded to the Booker who finalized the ticket.
   - `Direct_Bonus = T * 0.7` [1].
2. **Indirect Fund (30%):** Pooled for support staff (Recheck, Check-in, Debt Matching, Invoicing).
   - `Indirect_Fund = T * 0.3` [1].

### Indirect Distribution via KPI:
Individual indirect bonus is proportional to their KPI points within that specific booking [2].
- `Individual_Bonus = (Indirect_Fund / Total_Booking_KPI_Points) * Individual_KPI_Points` [2].

---

## 5. Implementation Rules & Constraints
- **Eligibility:** Bonus is only calculated once the flight date has passed (`Flight_Date < Current_Date`) [1].
- **Effective Date:** Applies to bookings issued from **July 1st, 2026** [1].
- **Refund Policy (PH):**
    - **Positive Refund (Profit):** Calculate bonus as a normal booking if `P_dv > MIN_SERVICE_FEE` [1].
    - **Negative Refund:** Average service fee is recalculated: `P_dv = (Total Booking Revenue + Total Refund Revenue) / Ticket Count`. Bonus only applies if this new `P_dv > MIN_SERVICE_FEE` [1].
- **Other Transactions:** Add-on services (Baggage, Date Change) must meet a minimum revenue of **110,000 VND/customer** per change to qualify for bonus [1].