<table width="100%" cellspacing="15" border="0" style="font-family:Tahoma, sans-serif;">
    <tr>
        <td align="center" style="padding:10px;font-size:18px;background-color:#EDEDED;" colspan="3">
            <strong>Packing Slip for Order #{{ $data->order_id }}-{{ $data->id }} </strong>
        </td>
    </tr>

    <tr>

        <td width="10%">
            @php  $filePath = storage_path("app/public/logoImage/$logo");
    $logo = base64_encode(file_get_contents($filePath)); @endphp
            <img width="150" height="110" src="data:image/png;base64,{{ $logo }}" alt="" />
        </td>
        <td width="50%">
            <strong>{{ $data->emp_first_name }} {{ $data->emp_last_name }}</strong><br />{{ $data->emp_add_1 }}
            @if (!empty($data->emp_add_2))
                {{ $data->emp_add_2 }}
            @endif <br /> {{ $data->emp_city }}, {{ $data->emp_state }} {{ $data->emp_pcode }}
            <br />
            US
        </td>

        <td width="40%">
            <strong>Order ID</strong>: #{{ $data->order_id }}-{{ $data->id }} <br />
            <strong>Order Date</strong>: {{ $data->created_at->format('d/m/Y') }}
        </td>


    </tr>

    <tr>
        <td style="text-align:center">
            <strong>
                <h3> Ship To: </h3>
            </strong>
        </td>
        <td>

            <strong>{{ $data->receipient_name }}</strong><br />{{ $data->receipient_add_1 }} @if (!empty($data->receipient_add_2))
                {{ $data->receipient_add_2 }}
            @endif
            <br /> {{ $data->receipient_city }} , {{ $data->receipient_state }} {{ $data->receipient_zip }} <br />
            US
        </td>
    </tr>

    @if (!empty($data->custom_msg))
        <tr>
            <td colspan="3" style="padding-top:25px;">
                <strong>
                    <h3>Important Message from {{ $data->receipient_name }}</h3>
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="3"
                style="border-top:1px solid black; border-bottom:1px solid black; padding:20px;font-size:18px;">
                <p>
                    {{ $data->custom_msg }}
                </p>
            </td>
        </tr>
    @endif

    <!-- INSTRUCTIONS - START -->

    <tr>
        <td colspan="3" style="padding-top:25px;">
            <strong>
                <h2 style="">Instructions to Return the {{ $data->type_of_equip }}</h2>
            </strong>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="font-size:18px;">
            <ul>
                <li>
                    <strong>Prepare the {{ $data->type_of_equip }}:</strong> Power it off, and securely pack it along
                    with all accessories (e.g., charger, cables).
                </li><br />
                <li>
                    <strong>Attach the Label:</strong> Print and affix the provided shipping label to the package.
                </li><br />
                <li>
                    <strong>Ship the Package:</strong> Drop it off at the specified carrier location or schedule a
                    pickup. Retain the tracking number and notify the company.
                </li><br />
                <li>
                    <strong>Confirm Receipt:</strong> Wait for confirmation of delivery from the company and follow up
                    if needed.
                </li>
            </ul>
        </td>
    </tr>

    <!-- INSTRUCTIONS - END -->
</table>
