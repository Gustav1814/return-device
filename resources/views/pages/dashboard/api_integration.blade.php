@extends('layouts.home')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>API Integration</h1>

        </div><!-- End Page Title -->


        <section class="rr-section-api-integration">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        <nav class="sidenav">
                            <!-- <a class="navbar-brand" href="https://www.remoteretrieval.com"><img src="https://www.remoteretrieval.com/wp-content/themes/remoteretrieval-theme/assets/img/logo.png" alt="" srcset="https://www.remoteretrieval.com/wp-content/themes/remoteretrieval-theme/assets/img/logo.png"></a> -->
                            <ul>
                                <li class="sidebar-heading"><a href="#introduction">Introduction</a></li>
                                <hr>
                                <li class="sidebar-heading">API Endpoints</li>
                                <li><a href="#validate-user"><span class="get_method_box">GET</span> Validate User</a></li>
                                <li><a href="#create-order"><span class="post_method_box">POST</span> Create Order </a></li>
                                <!-- <li><a href="#pending-orders"><span class="get_method_box">GET</span> Pending Orders </a></li> -->
                                <!-- <li><a href="#new-orders"><span class="get_method_box">GET</span> New Orders </a></li> -->
                                <!-- <li><a href="#inprogress-orders"><span class="get_method_box">GET</span> In-progress Orders </a></li> -->
                                <li><a href="#all-orders"><span class="get_method_box">GET</span> All Orders </a></li>
                                <!-- <li><a href="#completed-orders"><span class="get_method_box">GET</span> Completed Orders </a></li> -->
                                <li><a href="#order-detail"><span class="get_method_box">GET</span> Order Details</a></li>
                                <li><a href="#company-detail"><span class="get_method_box">GET</span> Company Details</a>
                                </li>
                                <li><a href="#device-prices"><span class="get_method_box">GET</span> Device Prices</a></li>
                                <hr>
                                <li title="Postman Collection" class="sidebar-heading"><a href="#api-collection">API
                                        Collection</a></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="col-lg-9 my-4">
                        <div class="row">
                            <div class="col-md-6">
                                <section id="introduction">
                                    <h3> Introduction</h3>
                                    <p class="api_det_class"> Welcome to the Return Device API documentation. This API
                                        allows you to manage orders through
                                        the mentioned endpoints. You'll need to authenticate using an API token to access
                                        the endpoints.
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <section id="validate-user">
                                    <h3> Validate User</h3>
                                    <p class="api_det_class">Send a request with the API token key to this endpoint to
                                        validate the user. If the key is
                                        invalid, a failure response will be generated; otherwise, a success response will be
                                        returned.
                                    </p>
                                    <br>

                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc" style="display:block;">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> Validate User</h5>
                                        <p>This API will validate user </p>
                                        <pre><code>GET /api/v1/validate/user</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class="codeBlock">
{
    "message": "Valid Key!",
    "email": "abc@example.com",
    "status": "Success",
    "response_code": 200
}
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <section id="create-order">
                                    <h3> Create Order</h3>
                                    <p class="api_det_class">
                                        This API endpoint allows users to create a new order. By sending the necessary order
                                        details to this endpoint,
                                        the system will generate and store a new order in the database, returning a
                                        confirmation response upon successful
                                        creation. This endpoint requires authentication and specific parameters to be
                                        included in the request body to ensure
                                        the correct and complete order information is provided.
                                    </p>
                                    <br>

                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>

                                    <p class="authorizClass">➤ REQUEST BODY SCHEMA </p>
                                    <p class="apikey_doc" style="display:none;">
                                        For creating order, must follow payload sample. It is sample for creating two
                                        orders. You
                                        can create
                                        single order or multiple orders by this API.
                                        <br><br>
                                        <strong class="apicontent_h">✧ type_of_equipment:</strong>
                                        <br>
                                        There are four type of equipments: 1) Laptop 2) Monitor 3) Cell Phone 4) Tablet
                                        <br><br>

                                        <strong class="apicontent_h">✧ order_type:</strong>
                                        <br>
                                        There are two type of orders: 1) Return To Company 2) Recycle with Data Destruction
                                        <br><br>

                                        <strong class="apicontent_h">✧ return_add_srv (optional):</strong>
                                        <br>
                                        There are two additional service options when the order type is 'Return To Company':
                                        <br />
                                        1) Data destruction and return to company
                                        <br />
                                        2) Data destruction and delivery to a new employee
                                        <br />
                                        To select one of these options, pass a value of 1 or 2:<br />
                                        1 corresponds to data destruction and return to company<br />
                                        2 corresponds to data destruction and delivery to a new employee
                                        <br />
                                        Note: If you select option 2, you must also include the new employee's details in
                                        the request.
                                        <br><br>

                                        <strong class="apicontent_h">✧ ins_active (optional):</strong>
                                        <br>
                                        This is optional. If you want to insure the product, set this flag to '1' and ensure
                                        a numeric
                                        value is provided in ins_amount.
                                        <br><br>

                                        <strong class="apicontent_h">✧ ins_amount (optional):</strong>
                                        <br>
                                        Enter the insurance amount for the product here. If ins_active is enabled, you must
                                        provide a value here.
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.email:</strong>
                                        <br>
                                        Employee email address
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.name:</strong>
                                        <br>
                                        Employee full name
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_line_1:</strong>
                                        <br>
                                        Employee Address in line 1
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_line_2:</strong>
                                        <br>
                                        Employee Address in line 2, It is not mandatory field
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_city:</strong>
                                        <br>
                                        Employee city
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_state:</strong>
                                        <br>
                                        Employee state
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_country:</strong>
                                        <br>
                                        Employee country
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.address_zip:</strong>
                                        <br>
                                        Employee zip
                                        <br><br>

                                        <strong class="apicontent_h">✧ employee_info.phone:</strong>
                                        <br>
                                        Employee phone
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_person_name:</strong>
                                        <br>
                                        Company person name
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_company_name:</strong>
                                        <br>
                                        Company name
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_line_1:</strong>
                                        <br>
                                        Company address in line 1.
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_line_2:</strong>
                                        <br>
                                        Company address in line 2, it is not mandatory field.
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_city:</strong>
                                        <br>
                                        Company city
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_state:</strong>
                                        <br>
                                        Company state
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_country:</strong>
                                        <br>
                                        Company country
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.return_address_zip:</strong>
                                        <br>
                                        Company zip
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.email:</strong>
                                        <br>
                                        Company email
                                        <br><br>

                                        <strong class="apicontent_h">✧ company_info.phone:</strong>
                                        <br>
                                        Company phone
                                        <br><br>




                                        <strong>Note</strong>: If you select return_add_srv as 2, you must also include the
                                        new employee's details in the request
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.first_name:</strong>
                                        <br>
                                        New employee first name
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.last_name:</strong>
                                        <br>
                                        New employee last name
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.phone:</strong>
                                        <br>
                                        New employee phone
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.email:</strong>
                                        <br>
                                        New employee Email
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.address_line_1:</strong>
                                        <br>
                                        New employee address line 1
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.address_city:</strong>
                                        <br>
                                        New employee city
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.address_state:</strong>
                                        <br>
                                        New employee state
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.address_zip:</strong>
                                        <br>
                                        New employee zip
                                        <br><br>
                                        <strong class="apicontent_h">✧ new_employee_info.address_country:</strong>
                                        <br>
                                        New employee country
                                        <br><br>

                                        <strong class="apicontent_h">✧ new_employee_info.newemp_msg:</strong>
                                        <br>
                                        New employee message
                                        <br><br>




                                        <br>
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="post_method_box">POST</span> Create Order</h5>
                                        <pre><code>POST /api/v1/create-order</code></pre>

                                        <h5>Payload</h5>
                                        <pre><code class="codeBlock">
{
    "orders": [
        {
        "type_of_equipment": "Laptop",
        "order_type": "Return To Company",
        "employee_info": {
            "email": "kennethdavis@example.com",
            "name": "Kenneth Davis",
            "address_line_1": "1734 Steele Street",
            "address_line_2": "Apt 10A",
            "address_city": "Houston",
            "address_state": "TX",
            "address_country": "United States",
            "address_zip": "77001",
            "phone": "1231231234"
        },
        "company_info": {
            "return_person_name": "Dorothy Buchanan",
            "return_company_name": "BigCo",
            "return_address_line_1": "4522 Hanover Street",
            "return_address_line_2": "Ste 120",
            "return_address_city": "San Antonio",
            "return_address_state": "TX",
            "return_address_country": "United States",
            "return_address_zip": "78015",
            "email": "it-team@example.com",
            "phone": "1231231234"
        }
        },
        {
        "type_of_equipment": "Laptop",
        "order_type": "Return To Company",
        "employee_info": {
            "email": "kennethdavis@example.com",
            "name": "Kenneth Davis",
            "address_line_1": "1734 Steele Street",
            "address_line_2": "Apt 10A",
            "address_city": "Houston",
            "address_state": "TX",
            "address_country": "United States",
            "address_zip": "77001",
            "phone": "1231231234"
        },
        "company_info": {
            "return_person_name": "Dorothy Buchanan",
            "return_company_name": "BigCo",
            "return_address_line_1": "4522 Hanover Street",
            "return_address_line_2": "Ste 120",
            "return_address_city": "San Antonio",
            "return_address_state": "TX",
            "return_address_country": "United States",
            "return_address_zip": "78015",
            "email": "it-team@example.com",
            "phone": "1231231234"
        }
        }
    ]
}
                                        </code></pre>

                                        <h4>Response</h4>
                                        <pre><code class="codeBlock">
{
    "order": "212",
    "message": "Order has created!",
    "status": "Success",
    "response_code": 200
}
                                            </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="col-md-6">
                                <section id="pending-orders">
                                    <h3> Pending Orders</h3>
                                    <p class="api_det_class">
                                        This section provides a detailed list of all pending orders. These are the orders for which the payment process
                                        has not been completed. Pending orders indicate transactions that are currently on hold, awaiting the completion
                                        of payment before they can move forward in the fulfillment process.
                                    </p>
                                    <br />

                                    <p class="authorizClass">&#10148; AUTHORIZATION</strong> > API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br /><br />
                                        Authentication is performed using an API key, which can be obtained from the Enterprise
                                        Remote Retriever portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br /><br />
                                        <code>Authorization: Bearer < API_KEY ></code>
                                        <br />
                                    </p>

                                    <p class="authorizClass">&#10148; QUERY PARAMETERS</p>

                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">page:</strong>
                                        Results are paginated up to 25 per page. It can pass as query string as below code:
                                        <br /><br />
                                        <code>?page=1</code>
                                        <br />
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> Pending Orders</h5>
                                        <pre><code>GET /api/v1/pending-orders</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class ="codeBlock">
[
    {
        "order_id": 209,
        "employee_info": {
            "email": "kennethdavis@example.com",
            "name": "Kenneth Davis",
            "address_line_1": "1734 Steele Street",
            "address_line_2": "1734 Steele Street",
            "city": "Arlington Heights",
            "state": "IL",
            "zip": "60005"
        },
        "company_info": {
            "email": "it-team@example.com",
            "name": "Dorothy Buchanan",
            "address_line_1": "4522 Hanover Street",
            "address_line_2": "",
            "city": "New York",
            "state": "NY",
            "zip": "10016"
        },
        "shipments": {
            "device_type": "Laptop",
            "send_status": "---",
            "return_status": "---"
        }
    },
]
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div> -->
                        <!-- <div class="row">
                            <div class="col-md-6">
                                <section id="new-orders">
                                    <h3> New Orders</h3>
                                    <p class="api_det_class">This section contains a list of new orders. New orders are transactions where payment has
                                        already been completed. These orders are ready to move forward in the processing workflow as
                                        the payment has been successfully received.
                                    </p>
                                    <br />

                                    <p class="authorizClass">&#10148; AUTHORIZATION</strong> > API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br /><br />
                                        Authentication is performed using an API key, which can be obtained from the Enterprise
                                        Remote Retriever portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br /><br />
                                        <code>Authorization: Bearer < API_KEY ></code>
                                        <br />
                                    </p>

                                    <p class="authorizClass">&#10148; QUERY PARAMETERS</p>

                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">page:</strong>
                                        Results are paginated up to 25 per page. It can pass as query string as below code:
                                        <br /><br />
                                        <code>?page=1</code>
                                        <br />
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> New Orders</h5>
                                        <pre><code>GET /api/v1/new-orders</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class ="codeBlock">
[
    {
        "order_id": 209,
        "employee_info": {
            "email": "kennethdavis@example.com",
            "name": "Kenneth Davis",
            "address_line_1": "1734 Steele Street",
            "address_line_2": "1734 Steele Street",
            "city": "Arlington Heights",
            "state": "IL",
            "zip": "60005-4182"
        },
        "company_info": {
            "email": "it-team@example.com",
            "name": "Dorothy Buchanan",
            "address_line_1": "4522 Hanover Street",
            "address_line_2": "",
            "city": "New York",
            "state": "NY",
            "zip": "10016-1209"
        },
        "shipments": {
            "device_type": "Laptop",
            "send_status": "---",
            "return_status": "---"
        }
    },
]
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div> -->
                        <!-- <div class="row">
                            <div class="col-md-6">
                                <section id="inprogress-orders">
                                    <h3> In-progress Orders</h3>
                                    <p class="api_det_class">
                                        This section contains a list of in-progress orders. In-progress orders are those orders
                                        where the return process has already been initiated. However, the device has not yet been
                                        physically returned. These orders are currently being processed and are awaiting the return
                                        of the device to complete the order.
                                    </p>
                                    <br />

                                    <br />

                                    <p class="authorizClass">&#10148; AUTHORIZATION</strong> > API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br /><br />
                                        Authentication is performed using an API key, which can be obtained from the Enterprise
                                        Remote Retriever portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br /><br />
                                        <code>Authorization: Bearer < API_KEY ></code>
                                        <br />
                                    </p>

                                    <p class="authorizClass">&#10148; QUERY PARAMETERS</p>

                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">page:</strong>
                                        Results are paginated up to 25 per page. It can pass as query string as below code:
                                        <br /><br />
                                        <code>?page=1</code>
                                        <br />
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> In-progress Orders</h5>
                                        <pre><code>GET /api/v1/inprogress-orders</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class ="codeBlock">
[
    {
        "order_id": 209,
        "employee_info": {
            "email": "kennethdavis@example.com",
            "name": "Kenneth Davis",
            "address_line_1": "1734 Steele Street",
            "address_line_2": "1734 Steele Street",
            "city": "Arlington Heights",
            "state": "IL",
            "zip": "60005-4182"
        },
        "company_info": {
            "email": "it-team@example.com",
            "name": "Dorothy Buchanan",
            "address_line_1": "4522 Hanover Street",
            "address_line_2": "",
            "city": "New York",
            "state": "NY",
            "zip": "10016-1209"
        },
        "shipments": {
            "device_type": "Laptop",
            "send_status": "TRANSIT",
            "return_status": "TRANSIT"
        }
    },
]
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col-md-6">
                                <section id="all-orders">
                                    <h3> All Orders</h3>
                                    <p class="api_det_class">This API fetches complete order list with different statuses:
                                    </p>
                                    <p class="api_det_class"><strong>Payment Status:</strong></p>
                                    <ul>
                                        <li>
                                            <p class="api_det_class">If the `payment_status` is "Completed," it confirms
                                                that the payment for the order has been successfully processed. The customer
                                                has fully paid for the order, and no further payment action is required.</p>
                                        </li>
                                        <li>
                                            <p class="api_det_class">If the `payment_status` is "Pending," it indicates
                                                that the payment is still in progress or has not been completed. This could
                                                mean the payment is awaiting confirmation, or the customer has not yet
                                                provided payment details.</p>
                                        </li>
                                    </ul>
                                    <p class="api_det_class"><strong>Order Status:</strong></p>
                                    <ul>
                                        <li>
                                            <p class="api_det_class">If the `order_status` is "Ship to Employee: Pending,"
                                                it signifies that a shipping label for the order has not yet been created.
                                                This means the order is not yet ready to be shipped to the employee, and
                                                further actions may be needed to prepare the shipment.</p>
                                        </li>
                                        <li>
                                            <p class="api_det_class">If the `order_status` is "Ship to Employee: SHIPPED,"
                                                it confirms that a shipping label has been created, and the order has been
                                                shipped to the employee. The package is now in transit to the employee's
                                                location.</p>
                                        </li>
                                        <li>
                                            <p class="api_det_class">If the `order_status` is "Box Delivered to Employee:
                                                'STATUS'," it provides the current status of the box that was sent to the
                                                employee. This status could vary, showing whether the box is in transit,
                                                delivered, or any other relevant delivery status.</p>
                                        </li>
                                        <li>
                                            <p class="api_det_class">If the `order_status` is "Ship to Company: 'STATUS',"
                                                it reflects the current status of the device that has been sent back to the
                                                company. This could indicate whether the device is in transit, received, or
                                                any other pertinent status related to the return process.</p>
                                        </li>
                                    </ul>
                                    <p class="api_det_class">This detailed breakdown helps in understanding the various
                                        stages of an order and payment processing, ensuring transparency and clarity in the
                                        order fulfillment and return processes.</p>
                                    <br>
                                    <br>
                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>

                                    <p class="authorizClass">➤ QUERY PARAMETERS</p>

                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">cursor:</strong>
                                        Results are paginated up to 25 per page. It returns URL link from response for next
                                        page.
                                        <br><br>
                                        <code>?cursor=LINK</code>
                                        <br>
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> All Orders</h5>
                                        <pre><code>GET /api/v1/orders</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class="codeBlock">
{
    "next": null,
    "previous": null,
    "results": [
        {
            "order_id": 220,
            "payment_status": "Completed",
            "order_status": "Ship to Company: DELIVERED ",
            "employee_info": {
                "email": "jycypa@example.com",
                "name": "Fay Mooney",
                "address_line_1": "1734 Steele Street",
		"address_line_2": "",
		"city": "Arlington Heights",
		"state": "IL",
		"zip": "60005"
            },
            "company_info": {
	    	"email": "it-team@example.com",
	    	"name": "Dorothy Buchanan",
	    	"address_line_1": "4522 Hanover Street",
	    	"address_line_2": "",
	    	"city": "New York",
	    	"state": "NY",
	    	"zip": "10016"
            },
            "shipments": {
                "device_type": "Laptop",
                "send_status": "DELIVERED",
                "return_status": "DELIVERED"
            }
        }
    ]
}
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <section id="order-detail">
                                    <h3> Order Details</h3>
                                    <p class="api_det_class">
                                        This endpoint allows you to retrieve specific details and information. By sending a
                                        request to this endpoint,
                                        you can obtain detailed data of order. It is designed to provide precise and
                                        targeted information to meet your needs.

                                    </p>
                                    <br>

                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>

                                    <p class="authorizClass">➤ QUERY PARAMETERS</p>

                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">order ID:</strong>
                                        For getting order details, must send order ID for required order details
                                        <br><br>
                                        <code>?oid=ORDER_ID</code>
                                        <br>
                                    </p>

                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> Order Details</h5>
                                        <pre><code>GET /api/v1/device_returns?oid=ORDER_ID</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class="codeBlock">
[
    {
        "order_id": 148,
        "employee_info": {
            "email": "rakygotoh@example.com",
            "name": "Nora Navarro",
            "address_line_1": "122 Burrs Rd",
            "address_line_2": "Suite C",
            "city": "Westampton",
            "state": "NJ",
            "zip": "08060"
        },
        "company_info": {
            "email": "support@example.com",
            "name": "Return Device",
            "address_line_1": "122 Burrs Rd",
            "address_line_2": null,
            "city": "Westampton",
            "state": "NJ",
            "zip": "08060"
        },
        "shipments": {
            "device_type": "Laptop",
            "send_status": "TRANSIT",
            "return_status": "TRANSIT"
        }
    }
]
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <section id="company-detail">
                                    <h3> Company Details</h3>
                                    <p class="api_det_class">
                                        To retrieve detailed information about a company, use this endpoint. This endpoint
                                        provides comprehensive data
                                        on the specified company, including its name, address, contact information, and
                                        other relevant details.
                                    </p>
                                    <br>

                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> Company Details</h5>
                                        <pre><code>GET /api/v1/company-details</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class="codeBlock">
{
    "company_name": "company HB",
    "company_email": "abc@example.com",
    "address_1": "abc address 123",
    "address_2": null,
    "city": "East Brunswick",
    "state": "NJ",
    "zip": "08816",
    "created_date": "24-Jun-2024"
}
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>


                        <!-- DEVICE PRICES - START -->
                        <div class="row">
                            <div class="col-md-6">
                                <section id="device-prices">
                                    <h3> Device Prices</h3>
                                    <p class="api_det_class">
                                        To get device prices dynamically, use this API. This API provides real-time pricing
                                        data for all supported devices, ensuring accurate and up-to-date information.
                                    </p>
                                    <br>

                                    <p class="authorizClass">➤ AUTHORIZATION &gt; API</p>
                                    <p class="apikey_doc">
                                        <strong class="apicontent_h">HTTP: API Key</strong>
                                        <br><br>
                                        Authentication is performed using an API key, which can be obtained from the
                                        Return Device portal. When making an API call, the key should be provided in the
                                        Authorization header as follows:
                                        <br><br>
                                        <code>Authorization: Bearer &lt; API_KEY &gt;</code>
                                        <br>
                                    </p>
                                </section>
                            </div>
                            <div class="col-md-6">
                                <section id="endpoints">
                                    <h3>Endpoint</h3>
                                    <article id="validate-user">
                                        <h5><span class="get_method_box">GET</span> Device Prices</h5>
                                        <pre><code>GET /api/v1/get-device-prices</code></pre>
                                        <h5>Response</h5>
                                        <pre><code class="codeBlock">
[{"equipment_type":"Laptop","order_amount":77,"option_lbl":"Laptop($77)"},
{"equipment_type":"Monitor","order_amount":99,"option_lbl":"Monitor($99)"},
{"equipment_type":"Monitor_27","order_amount":149,"option_lbl":"Monitor_27($149)"},
{"equipment_type":"Tablet","order_amount":59,"option_lbl":"Tablet($59)"},
{"equipment_type":"Cell Phone","order_amount":59,"option_lbl":"Cell Phone($59)"}]
                                    </code></pre>
                                    </article>
                                </section>
                            </div>
                        </div>
                        <!-- DEVICES PRICES - END -->



                        <div class="row">
                            <div class="col-md-6">
                                <section id="api-collection">
                                    <h3> API Collection</h3>
                                    <p class="api_det_class">
                                        To download the API collection, please follow these steps. First, navigate to the
                                        API
                                        documentation page. Click on the button to initiate the download.
                                        The API collection will be downloaded as a file, which can be imported into your
                                        preferred API
                                        testing tool, such as Postman. This collection contains all the endpoints and sample
                                        requests
                                        to help you get started with integrating and testing the API efficiently.
                                    </p>
                                    ✪ <a style="color:#000" href="{{ route('api.collection.download') }}">Download API Collection</a>
                                    <br>
                                </section>
    </main>
@stop
