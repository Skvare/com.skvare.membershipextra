# com.skvare.membershipextra

This extension gives multiple options to control membership renewal. For the fixed membership type,
you can restrict renewal until the rollover date is over. For the rolling membership type,
you can set a day and unit for restricting membership renewal before the end date.

You can restrict the membership type signup or renewal based on group contact if the contact is not part of the group.

This extension provides a custom membership search that contains membership types (include, exclude),
membership status, and dates. You can contact those who have Membership Type A but don't have Membership Type B.

## Requirements

* PHP v7.2+
* CiviCRM (5.21)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.skvare.membershipextra@https://github.com/skvare/com.skvare.membershipextra/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/skvare/com.skvare.membershipextra.git
cv en membershipextra
```

## Usage

You can visit Membership Type and edit an existing membership type. (Administer CiviCRM › CiviMember › Membership Types)

Fixed Membership Type setting:

![Screenshot](/images/fixed_membership.png)


Rolling Membership Type Settings:

![Screenshot](/images/rolling_membership.png)


Front end Validation for Additional Renewal:

![Screenshot](/images/stop_renewal.png)

Front end Validation for Membership Type is restricted to Group Contact:

![Screenshot](/images/group_restriction.png)


Membership Include or Exclude Custom Search:

![Screenshot](/images/membership_custom_search.png)

