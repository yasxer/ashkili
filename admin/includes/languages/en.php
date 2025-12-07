<?php
// English translations
$lang = [
    // Navigation and Common
    'dashboard' => 'Dashboard',
    'logout' => 'Logout',
    'admin_panel' => 'Admin Panel',
    'language' => 'Language',
    'select_language' => 'Select Language',
    
    // Dashboard
    'pending_complaints' => 'Pending Complaints',
    'registered_clients' => 'Registered Clients',
    'tracked_packages' => 'Tracked Packages',
    'total_complaints' => 'Total Complaints',
    'quick_actions' => 'Quick Actions',
    'recent_activity' => 'Recent Activity',
    
    // Actions
    'manage_packages' => 'Manage Packages',
    'manage_clients' => 'Manage Clients',
    'manage_complaints' => 'Manage Complaints',
    'add_client' => 'Add Client',
    'add_package' => 'Add Package',
    
    // Package Management
    'package_management' => 'Package Management',
    'filter_packages' => 'Filter Packages',
    'tracking_code' => 'Tracking Code',
    'client_name' => 'Client Name',
    'status' => 'Status',
    'expedition_date' => 'Expedition Date',
    'delivery_date' => 'Delivery Date',
    'location' => 'Location',
    'price' => 'Price',
    'weight' => 'Weight',
    'phone' => 'Phone',
    'actions' => 'Actions',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'filter' => 'Filter',
    'reset' => 'Reset',
    'no_packages_found' => 'No packages found.',
    
    // Client Management
    'client_management' => 'Client Management',
    'add_new_client' => 'Add New Client',
    'delete_clients' => 'Delete Clients',
    'client_id' => 'Client ID',
    'username' => 'Username',
    'registration_date' => 'Registration Date',
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Confirm Password',
    'full_name' => 'Full Name',
    
    // Add Package
    'add_new_package' => 'Add New Package',
    'package_info' => 'Package Information',
    'client_info' => 'Client Information',
    'delivery_info' => 'Delivery Information',
    'current_status' => 'Current Status',
    'estimated_delivery' => 'Estimated Delivery',
    'complementary_phone' => 'Complementary Phone',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'required_fields' => 'All required fields must be filled.',
    'tracking_code_exists' => 'This tracking code already exists.',
    'package_added_success' => 'Package added successfully!',
    
    // Status values
    'expedited' => 'Expedited',
    'in_transit' => 'In Transit',
    'delivered' => 'Delivered',
    'in_progress' => 'In Progress',
    'returned' => 'Returned',
    'pending' => 'Pending',
    'resolved' => 'Resolved',
    
    // Form labels and inputs
    'region' => 'Region',
    'select_region' => 'Select region',
    'all_regions' => 'All regions',
    'start_date' => 'Start date',
    'end_date' => 'End date',
    'select_status' => 'Select status',
    'all_statuses' => 'All statuses',
    'search_placeholder' => 'Search...',
    
    // Table headers
    'id' => 'ID',
    'name' => 'Name',
    'date' => 'Date',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',
    
    // Buttons
    'apply_filters' => 'Apply filters',
    'clear_filters' => 'Clear filters',
    'export' => 'Export',
    'import' => 'Import',
    'refresh' => 'Refresh',
    'print' => 'Print',
    
    // Messages
    'no_results' => 'No results found',
    'loading_data' => 'Loading data...',
    'operation_successful' => 'Operation successful',
    'operation_failed' => 'Operation failed',
    'please_wait' => 'Please wait...',
    'confirm_action' => 'Confirm action',
    'are_you_sure' => 'Are you sure?',
    
    // Pagination
    'showing_results' => 'Showing %d to %d of %d results',
    'items_per_page' => 'Items per page',
    'page' => 'Page',
    'of' => 'of',
    'first' => 'First',
    'last' => 'Last',
    
    // File operations
    'upload' => 'Upload',
    'download' => 'Download',
    'file_selected' => 'File selected',
    'no_file_selected' => 'No file selected',
    
    // Time and dates
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'this_week' => 'This week',
    'this_month' => 'This month',
    'this_year' => 'This year',
    'last_week' => 'Last week',
    'last_month' => 'Last month',
    'last_year' => 'Last year',
    
    // Activity
    'new_package_added' => 'New package added',
    'new_client_registered' => 'New client registered',
    'complaint_resolved' => 'Complaint resolved',
    'package_delivered' => 'Package delivered',
    'hours_ago' => '%d hours ago',
    'days_ago' => '%d day(s) ago',
    
    // Time units
    '2_hours_ago' => '2 hours ago',
    '5_hours_ago' => '5 hours ago',
    '1_day_ago' => '1 day ago',
    '2_days_ago' => '2 days ago',
    
    // Additional form elements
    'comment' => 'Comment',
    'add_comment' => 'Add a note...',
    'select_status' => '-- Select status --',
    'required_field' => 'Required field',
    'optional' => 'Optional',
    
    // Status options
    'in_preparation' => 'In preparation',
    'expedited' => 'Expedited',
    'in_delivery' => 'In delivery',
    'delayed' => 'Delayed',
    'cancelled' => 'Cancelled',
    
    // Additional client management
    'no_clients_found' => 'No clients found',
    'add_first_client' => 'Start by adding a new client',
    'name' => 'Name',
    'phone' => 'Phone',
    'id' => 'ID',
    
    // JavaScript messages
    'edit_functionality_message' => 'Edit functionality to be implemented for client: ',
    'confirm_delete_client' => 'Are you sure you want to delete client "%s"?\n\nThis action is irreversible.',
    
    // Client creation form
    'all_fields_required' => 'All fields are required.',
    'password_min_length' => 'Password must contain at least 8 characters.',
    'username_exists' => 'This username already exists. Please choose another one.',
    'client_created_success' => 'Client account has been created successfully! Username: ',
    'client_creation_error' => 'Error creating account: ',
    'full_name_client' => 'Client Full Name',
    'username_label' => 'Username',
    'initial_password' => 'Initial Password',
    'phone_number' => 'Phone Number',
    'create_client_account' => 'Create Client Account',
    
    // Additional form elements
    'example' => 'Example',
    'username_help' => 'Username must be unique and will be used for login',
    'minimum_8_chars' => 'Minimum 8 characters',
    'password_req_length' => 'At least 8 characters',
    'password_req_uppercase' => 'One uppercase letter',
    'password_req_lowercase' => 'One lowercase letter',
    'password_req_number' => 'One number',
    'or' => 'or',
    'phone_format' => 'Accepted format: 06XXXXXXXX or +212XXXXXXXXX',
    
    // Add package form
    'all_required_fields' => 'All required fields must be filled.',
    'tracking_code_exists' => 'This tracking code already exists.',
    'package_added_success' => 'Package added successfully!',
    'package_add_error' => 'Error adding package: ',
    'database_error' => 'Database error: ',
    'important_info' => 'Important Information',
    'fill_required_fields_info' => 'Fill all required fields (*) to register a new package in the system. Fields include: tracking code, client, recipient name, location, phone, price and weight.',
    'select_client' => '-- Select a client --',
    'client' => 'Client',
    'current_status' => 'Current Status',
    'expedition_date' => 'Expedition Date',
    'expected_delivery_date' => 'Expected Delivery Date',
    'client_name' => 'Client Name',
    'full_recipient_name' => 'Full recipient name',
    'delivery_wilaya' => 'Delivery Province',
    'select_wilaya' => 'Select a province',
    'save_package' => 'Save Package',
    'reset' => 'Reset',
    
    // Complaint management
    'complaint_management' => 'Complaint Management',
    'all_complaints' => 'All complaints',
    'advanced_filters' => 'Advanced filters',
    'process_complaint' => 'Process complaint',
    'complaint_id' => 'Complaint ID',
    'complaint_type' => 'Type',
    'complaint_date' => 'Date',
    'complaint_status' => 'Status',
    'complaint_description' => 'Description',
    'admin_response' => 'Admin Response',
    'process' => 'Process',
    'pending' => 'Pending',
    'in_progress' => 'In Progress',
    'resolved' => 'Resolved',
    'cancelled' => 'Cancelled',
    
    // Login page
    'fill_all_fields' => 'Please fill in all fields.',
    'invalid_email' => 'Invalid email address.',
    'account_inactive' => 'This account is disabled. Contact administrator.',
    'invalid_credentials' => 'Incorrect email or password.',
    'admin_login' => 'Admin Login',
    'admin_space' => 'ADMINISTRATOR AREA',
    'access_admin_panel' => 'Access the administration panel',
    'login' => 'Log in',
    'create_admin_account' => 'Create an admin account?',
    'register' => 'Sign up',
    'back_to_client_space' => 'Back to client area',
    
    // Additional client form translations
    'attention' => 'Attention',
    'public_registration_disabled' => 'Public registration page for clients has been disabled. All accounts must be created here.',
    'client_information' => 'Client Information',
    'fill_all_fields_create_account' => 'Fill all fields to create a new client account',
    'password_requirements_alert' => 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter and one number.',
    'username_min_length_alert' => 'Username must contain at least 3 characters.',
];
?>