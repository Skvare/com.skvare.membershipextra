# com.skvare.membershipextra

This extension give multiple option to control membership renewal. For Fixed Membership type, you can set restrict 
Renewal until Rollver date is over. For Rolling Membership type you can set day and unit for restrict membership renewal before end date.

You can restrict the Membership Type Signup/Renewal based on Group contact, If contact is not part of Group.

This Extension provide Custom Membership Search which contain Membership Type Include Exclude along with Memebership Status and their dates. You can find contact those have Membership Type A but don't have Membership Type B.


## Requirements

* PHP v7.2+
* CiviCRM (5.27)

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

You can visit Membership type, Edit existing membership type. (Administer CiviCRM › CiviMember › Membership Types)

Fixed Membership Type setting:

![Screenshot](/images/fixed_membership.png)


Rolling Membership Type settings:

![Screenshot](/images/rolling_membership.png)


Front end Validation for Additional Renewal:

![Screenshot](/images/stop_renewal.png)

Front end Validation for Membership Type restricted to Group Contact:

![Screenshot](/images/group_restriction.png)

