# Blood Donation System - Receiver Role Implementation

## Overview
Your blood donation system now has three user roles:
1. **Donor** - Can register as a blood donor and view emergency requests
2. **Hospital** - Can register and submit emergency blood requests
3. **Receiver** - Can register as a blood receiver and submit blood requests (NEW)

## What's New: Receiver Role

### Receiver Features:
- ✅ Register as a blood receiver with their blood type requirement
- ✅ Submit blood requests specifying quantity, blood type, and location
- ✅ View compatible donors who have registered in their area
- ✅ Track the status of their blood requests (Pending/Fulfilled)
- ✅ Receive notifications when compatible donors are available
- ✅ Dashboard to manage all requests and notifications

## Setup Instructions

### Step 1: Database Table Creation
1. Navigate to `http://localhost/project/blood/setup.php` in your browser
2. This will automatically create the required database tables:
   - `receivers` - Stores receiver profile information
   - `blood_requests` - Tracks blood requests and their status
3. After setup completes, delete the `setup.php` file for security

### Step 2: Database Tables Created
The setup.php creates the following tables:

**receivers table:**
```
- id: Primary key
- name: Receiver's full name
- city: Location
- phone: Contact number
- email: Email address
- blood_group_needed: Blood type they need
- registration_date: When they registered
- last_request_date: Last request submission time
- total_requests_fulfilled: Count of fulfilled requests
```

**blood_requests table:**
```
- id: Primary key
- receiver_id: Reference to receiver
- blood_group: Blood type requested
- quantity: Units needed
- city: Request location
- request_date: When request was submitted
- request_status: Pending/Fulfilled
- fulfilled_by_donor_id: Which donor fulfilled it (if any)
- fulfillment_date: When fulfilled
```

## User Flow

### For Receivers:
1. **Login**: Select "Receiver" role on login page
2. **Register**: Fill in receiver profile (name, city, phone, blood type needed)
3. **Submit Request**: Enter blood group, quantity, and city
4. **View Dashboard**: See all requests and compatible donors
5. **Track Status**: Monitor request status in real-time

### For Donors:
1. Donors see receiver blood requests in their emergency requests view
2. Donors can choose to help receivers based on their needs
3. Compatible donors are notified of new requests

### For Hospitals:
- No changes needed - hospitals continue as before

## Files Modified

### New Files Created:
- `/blood/receiver_register.php` - Receiver registration and blood request submission
- `/blood/receiver_dashboard.php` - Receiver dashboard showing requests and notifications
- `/blood/setup.php` - Database table setup script

### Files Updated:
- `login.php` - Added "Receiver" option to login roles
- `login.html` - Added "Receiver" option to login roles
- `navbar.php` - Added receiver navigation menu, changed "user" to "donor"
- `blood/index.php` - Changed role check from 'user' to 'donor'
- `blood/donor_register.php` - Changed role check from 'user' to 'donor'
- `blood/map.php` - Changed role check from 'user' to 'donor'
- `disease.php` - Changed role check from 'user' to 'donor'
- `waste.php` - Changed role check from 'user' to 'donor'

## Login Credentials Format

**Donor Login:**
- Select Role: "Donor"
- Email/Phone: Any value (basic auth)
- Password: Any value

**Hospital Login:**
- Select Role: "Hospital"
- Email/Phone: Any value
- Password: Any value

**Receiver Login:**
- Select Role: "Receiver"
- Email/Phone: Any value
- Password: Any value

## Receiver Registration Process

### Step 1: Complete Receiver Profile
Fill in the following information:
- Full Name
- City
- Phone Number
- Email (optional)
- Blood Group Needed

### Step 2: Submit Blood Request
After registration, submit a blood request:
- Blood Group Required
- Quantity (in units)
- City

## Dashboard Features

The Receiver Dashboard displays:
1. **Greeting** - Welcome message with receiver's blood type
2. **Blood Requests Table** - All requests with status
3. **Compatible Donors** - List of matching donors by blood type
4. **Quick Stats** - Pending requests, compatible donors, fulfilled requests
5. **Instructions** - How the system works

## Notification System

When a receiver submits a request:
- System finds all compatible donors
- Donors are notified of the new request
- Dashboard shows available donors for the receiver
- Status updates when donors assist

## Error Handling

The system includes:
- ✅ Automatic database table creation
- ✅ Error messages for failed submissions
- ✅ Role-based access control
- ✅ Input validation
- ✅ Session management

## Security Note

After running setup.php once:
1. Delete `/blood/setup.php` for security
2. System will not expose database creation interface
3. All subsequent operations go through proper pages

## Troubleshooting

### "Receiver not found" error
- Ensure receiver is registered first
- Check that database tables were created (run setup.php)

### "Access Denied" error
- Make sure you logged in with "Receiver" role
- Check that sessions are enabled

### Database tables not created
- Run `http://localhost/project/blood/setup.php` in browser
- Check database connection in `/blood/db.php`

## Testing Checklist

- [ ] Login with "Receiver" role
- [ ] Register as receiver
- [ ] Submit blood request
- [ ] View receiver dashboard
- [ ] See compatible donors
- [ ] Verify requests are saved
- [ ] Check database tables exist
- [ ] Test all error messages

## Notes

- The original "User" role has been renamed to "Donor" for clarity
- Blood requests are immediate (no queue system yet)
- Donors are notified through the dashboard
- System uses compatible blood type matching for notifications
