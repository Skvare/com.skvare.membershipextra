# com.skvare.membershipextra — Membership Extra Features

A CiviCRM extension that adds advanced controls to membership types for renewal windows,
upgrade paths, group restrictions, and custom membership reporting.

## Features

### 1. Renewal Window — Fixed Membership Types

For fixed-period memberships (e.g. January 1 – December 31 annually), you can block
early renewal until the **Fixed Period Rollover Day** is reached.

- Enable **"Limit renewal to 1 period ahead"** on the membership type.
- On the contribution page, submitting a renewal before the rollover day shows an error
  telling the member the earliest date they may renew.
- If the membership has already expired, renewal is always permitted regardless of the
  rollover day.

### 2. Renewal Window — Rolling Membership Types

For rolling memberships (duration relative to join date, e.g. 1 year from signup), you
can define how far in advance of the end date renewal is allowed.

- Set **"Disallow renewal until"** with a number and unit (days/months).
- Example: setting 30 days means renewal opens 30 days before the membership expires.
- Submitting a renewal before that window opens shows an error with the earliest
  allowed renewal date.

### 3. Membership Upgrade Levels

Controls which membership types a contact is allowed to change to on a public
contribution page, based on their current active membership type(s).

- Configure **"Member can change to"** with one or more target membership types.
- On form submission, the extension checks whether the submitted membership type is a
  valid upgrade from any of the contact's current active memberships.
- If the contact has multiple active memberships, the upgrade is allowed if **any one**
  of their current types permits it — the restriction only applies when no current type
  allows the selected upgrade.
- When blocked, the error message lists the allowed upgrade paths for each restricted type.

### 4. Group-Based Access Restriction

Limits membership signup or renewal to contacts who belong to specific CiviCRM groups.

- Configure **"Only allow members of groups"** with one or more groups.
- On form submission, if the contact is not a member of any of the required groups,
  they receive an error listing the required groups.
- Works for logged-in contacts whose group membership is known.

### 5. Unauthenticated Contact Check

Extends the group restriction to anonymous (not logged-in) form submissions.

- Enable **"Apply renewal restrictions to unauthenticated users"** on the membership type.
- When enabled, the extension attempts to identify the contact from submitted form fields
  using CiviCRM's unsupervised deduplication rules, then applies the group restriction.
- **Privacy note:** enabling this reveals whether a matching contact record exists for
  the submitted details, which may disclose information about existing members.

### 6. Custom Membership Search

A custom search available under **Search › Custom Searches** that lets administrators
find contacts based on complex membership criteria:

- **Include membership types** — contacts who have one or more of the selected types.
- **Exclude membership types** — contacts who do NOT have any of the selected types
  (useful for finding members of Type A who have not yet upgraded to Type B).
- **Membership status** (include/exclude) — filter by current membership status.
- **Date filters** — join date, start date, and end date ranges with relative date support.

Results show contact ID, type, name, email, phone, and membership type name.

### 7. Reports

Three custom CiviCRM reports are included:

| Report | Description |
|---|---|
| Membership Donation | Combines membership and contribution data. |
| Membership Renewal | Tracks upcoming and past renewals. |
| Separate Membership Amount | Reports membership fees separately from other contributions. |

---

## Settings Reference

All settings are configured per membership type at
**Administer CiviCRM › CiviMember › Membership Types** (edit an existing type).

| Setting | Applies to | Description |
|---|---|---|
| Member can change to | All types | Membership types this type is allowed to upgrade to. |
| Limit renewal to 1 period ahead | Fixed only | Block renewal before the Fixed Period Rollover Day. |
| Disallow renewal until N unit | Rolling only | Open the renewal window N days/months before expiry. |
| Only allow members of groups | All types | Restrict signup/renewal to contacts in these groups. |
| Apply renewal restrictions to unauthenticated users | All types | Extend group check to anonymous form submissions. |

Settings are stored in CiviCRM's settings store, namespaced per extension and membership
type, so they do not modify the core `civicrm_membership_type` schema.

---

## Requirements

- PHP 7.2+
- CiviCRM 5.65+

## Installation (CLI, Git)

```bash
git clone https://github.com/Skvare/com.skvare.membershipextra.git
cv en membershipextra
```

## Installation (CLI, Zip)

```bash
cd <extension-dir>
cv dl com.skvare.membershipextra@https://github.com/skvare/com.skvare.membershipextra/archive/master.zip
```

## Screenshots

Fixed Membership Type settings:

![Screenshot](/images/fixed_membership.png)

Rolling Membership Type settings:

![Screenshot](/images/rolling_membership.png)

Renewal restriction on contribution page:

![Screenshot](/images/stop_renewal.png)

Group restriction on contribution page:

![Screenshot](/images/group_restriction.png)

Custom membership search:

![Screenshot](/images/membership_custom_search.png)

Membership change Rule settings:

![Screenshot](/images/membership_change_rule_setting.png)


Membership change Rule error message:

![Screenshot](/images/membership_change_rule_error.png)


## License

AGPL-3.0 — see [LICENSE](http://www.gnu.org/licenses/agpl-3.0.html)

## Maintainer

Sunil Pawar, [Skvare](https://skvare.com) — sunil@skvare.com
