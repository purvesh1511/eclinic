@extends('backend.layouts.app')

@section('title')
    {{ __($module_title) }}
@endsection
@section('content')
    <div class="row">
        <x-backend.section-header>
            <x-slot name="toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('backend.appointments.index') }}" class="btn btn-primary" data-type="ajax"
                        data-bs-toggle="tooltip">
                        {{ __('appointment.back') }}
                    </a>
                </div>
                @php
                    $id = $appointment ? $appointment->id : 0;
                    $status = $appointment ? $appointment->status : null;
                    $pay_status = $appointment ? optional($appointment->appointmenttransaction)->payment_status : 0;
                @endphp
                @if ($pay_status == 1 && $status == 'checkout')
                    <div class="d-flex justify-content-end align-items-center ">

                        <a class="btn btn-primary"
                            href="{{ route('backend.appointments.download_invoice', ['id' => $appointment->id]) }}">
                            <i class="fa-solid fa-download"></i>
                            {{ __('appointment.lbl_download') }}
                        </a>
                    </div>
                @endif
            </x-slot>
        </x-backend.section-header>
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_patient_name') }}</span>
                            <div class="d-flex gap-3 align-items-center">

                                <img src="{{ optional($appointment->user)->profile_image ?? default_user_avatar() }}"
                                    alt="avatar" class="avatar avatar-70 rounded-pill">
                                <div class="text-start">
                                    <h5 class="m-0">{{ optional($appointment->user)->full_name ?? default_user_name() }}
                                    </h5>
                                    <span>{{ optional($appointment->user)->email ?? '--' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div>
                                <span class="d-block mb-2">{{ __('clinic.lbl_clinic_name') }}</span>
                                <h6 class="m-0"> <img
                                        src="{{ optional($appointment->cliniccenter)->file_url ?? 'default_file_url()' }}"
                                        alt="avatar" class="avatar avatar-30 rounded-pill me-2">
                                    {{ $appointment->cliniccenter ? optional($appointment->cliniccenter)->name : '--' }}
                                </h6>
                            </div>

                            <div>
                                <span class="d-block mb-2">{{ __('appointment.lbl_status') }}</span>
                                <h6 class="m-0">
                                    {{ ucwords(str_replace('_', ' ', $appointment->status === 'checkout' ? 'complete' : $appointment->status)) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class="row gy-3 mb-5">
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_appointment_date') }}</span>
                            <h6 class="m-0">{{ date($dateformate, strtotime($appointment->appointment_date ?? '--')) }}
                            </h6>
                        </div>
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_appointment_time') }}</span>
                            <h6 class="m-0">At
                                {{ $appointment->appointment_time
                                    ? \Carbon\Carbon::parse($appointment->appointment_time)->format($timeformate)
                                    : '--' }}
                            </h6>
                        </div>
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_doctor') }}
                                {{ __('appointment.lbl_name') }}</span>

                            @if ($appointment->doctor === null)
                                <h6 class="m-0">--</h6>
                            @else
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="{{ optional($appointment->doctor)->profile_image ?? default_user_avatar() }}"
                                        alt="avatar" class="avatar avatar-50 rounded-pill">
                                    <div class="text-start">
                                        <h6 class="m-0">
                                            {{ optional($appointment->doctor)->first_name . ' ' . optional($appointment->doctor)->last_name }}
                                        </h6>
                                        <span>{{ optional($appointment->doctor)->email ?? '--' }}</span>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    <div class="border-top"></div>
                    <div class="row gy-3 pt-5">
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_payment_status') }}</span>
                            @if (isset($appointment->appointmenttransaction->payment_status))
                                @if ($appointment->status === 'cancelled' && optional($appointment->appointmenttransaction)->payment_status == 1)
                                    <h6 class="m-0 mb-2 text-success">{{ __('appointment.refunded') }}</h6>
                                @elseif (optional($appointment->appointmenttransaction)->payment_status == 1)
                                    <h6 class="m-0 mb-2 text-success">{{ __('appointment.paid') }}</h6>
                                @elseif($appointment->status == 'cancelled' && $appointment->advance_paid_amount != 0)
                                    <h6 class="m-0 mb-2 text-success">{{ __('appointment.advance_refund') }}</h6>
                                @elseif(optional($appointment->appointmenttransaction)->payment_status == 0 &&
                                        optional($appointment->appointmenttransaction)->advance_payment_status == 1)
                                    <h6 class="m-0 mb-2 text-success">{{ __('appointment.advance_paid') }}</h6>
                                @else
                                    <h6 class="m-0 mb-2 text-secondary">{{ __('appointment.pending') }}</h6>

                                    <span class="d-block mb-1">{{ __('appointment.lbl_payment_method') }}</span>

                                    <h6 class="m-0  mb-2">Paid with
                                        {{ ucfirst(optional($appointment->appointmenttransaction)->transaction_type) }}
                                    </h6>
                                @endif
                            @else
                                <h6 class="m-0 text-danger">{{ __('appointment.failed') }}</h6>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_contact_number') }}</span>
                            <h6 class="m-0">{{ optional($appointment->user)->mobile ?? '--' }}</h6>
                        </div>
                        <div class="col-md-3">
                            <span class="d-block mb-1">{{ __('appointment.lbl_duration') }}</span>
                            <h6 class="m-0">{{ $appointment->duration ?? '--' }} min</h6>
                        </div>

                        @if ($appointment->media->isNotEmpty())
                            <div class="col-md-3">
                                <span class="d-block mb-1">{{ __('appointment.lbl_medical_report') }}</span>
                                <ul>
                                    @foreach ($appointment->media as $media)
                                        <li>
                                            <a href="{{ asset($media->getUrl()) }}" download>
                                                {{ $media->file_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if ($appointment->appointment_extra_info != '')
            <div class="col-md-3">
                <div class="card card-block card-stretch card-height">
                    <div class="card-body">
                        <div class="flex-column">
                            <h5>{{ __('appointment.lbl_addition_information') }}</h5>
                            <span class="m-0">{{ $appointment->appointment_extra_info }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card card-block card-stretch card-height">
                <div class=" card-header">
                    <h5 class="card-title mb-0">{{ __('appointment.price') }} {{ __('appointment.detail') }} </h5>
                </div>
                <div class="card-body">
                    @if ($appointment->patientEncounter == null)
                        <div class="d-flex align-items-center bg-body p-4 rounded">
                            <div class="detail-box bg-white rounded">
                                <img src="{{ optional($appointment->clinicservice)->file_url ?? default_file_url() }}"
                                    alt="avatar" class="avatar avatar-80 rounded-pill">
                            </div>

                            <div class="ms-3 w-100 row">
                                <div class="ms-3 w-100 d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <span>
                                            <b>{{ optional($appointment->clinicservice)->name }}</b>
                                            {{ optional($appointment->clinicservice)->description ?? ' ' }}
                                        </span>
                                    </div>
                                    @php


                                        if (
                                            optional($appointment->appointmenttransaction)->discount_type ===
                                            'percentage'
                                        ) {
                                            $payable_Amount =
                                                $appointment->service_price -
                                                $appointment->service_price *
                                                    (optional($appointment->appointmenttransaction)->discount_value /
                                                        100) ;
                                        } else {
                                            $payable_Amount =
                                                $appointment->service_price -
                                                optional($appointment->appointmenttransaction)->discount_value ;
                                        }
                                    @endphp
                                    @if (optional($appointment->appointmenttransaction)->discount_value > 0)
                                        <h5 class="mb-0 w-50 text-lg-end text-sm-start">
                                            {{ Currency::format($payable_Amount) }} @if (optional($appointment->appointmenttransaction)->discount_type === 'percentage')
                                                (<span>{{ optional($appointment->appointmenttransaction)->discount_value ?? '--' }}%
                                                    </<span>) off
                                                @else
                                                    (<span>{{ Currency::format(optional($appointment->appointmenttransaction)->discount_value) ?? '--' }}
                                                        </<span>)
                                                        off
                                            @endif
                                        </h5>
                                        <del>{{ Currency::format($appointment->service_price ) }}</del>
                                    @else
                                        <h5 class="mb-0 w-50 text-lg-end text-sm-start">
                                            {{ Currency::format($appointment->service_amount ) }}
                                        </h5>
                                    @endif
                                </div>
                                <!-- <div class="col-md-3 d-flex justify-content-end">
                                                                                                                                                                                                                                                                                                                                                                                                                            <h5 class="mb-0">{{ Currency::format($appointment->service_price) }}</h5>
                                                                                                                                                                                                                                                                                                                                                                                                                        </div> -->
                            </div>
                        </div>
                    @endif
                    @if (
                        $appointment->patientEncounter !== null &&
                            optional(optional($appointment->patientEncounter)->billingrecord)->billingItem != null)
                        @foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $billingItem)
                            <div class="d-flex align-items-center bg-body p-4 rounded">
                                <div class="detail-box bg-white rounded">
                                    <img src="{{ optional($billingItem->clinicservice)->file_url ?? default_file_url() }}"
                                        alt="avatar" class="avatar avatar-80 rounded-pill">
                                </div>

                                <div class="ms-3 w-100 d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <span><b>{{ optional($billingItem->clinicservice)->name }}</b>
                                            {{ optional($billingItem->clinicservice)->description ?? ' ' }}</span>

                                    </div>
                                    @php
                                        if ($billingItem->discount_type === 'percentage') {
                                            $payable_Amount =
                                                $billingItem->service_amount -
                                                $billingItem->service_amount * ($billingItem->discount_value / 100);
                                        } else {
                                            $payable_Amount =
                                                $billingItem->service_amount - $billingItem->discount_value;
                                        }

                                    @endphp
                                    @if ($billingItem->discount_value > 0)
                                        <h5 class="mb-0 w-50 text-lg-end text-sm-start"> <span
                                                class="fw-normal">{{ Currency::format($payable_Amount) }} *
                                                {{ $billingItem->quantity }} = </span>
                                            {{ Currency::format($billingItem->total_amount) }} @if ($billingItem->discount_type === 'percentage')
                                                (<span>{{ $billingItem->discount_value ?? '--' }}%</<span>)
                                                    off
                                                @else
                                                    (<span>{{ Currency::format($billingItem->discount_value) ?? '--' }}
                                                        </<span>)
                                                        off
                                            @endif
                                        </h5>
                                        <del>{{ Currency::format($billingItem->service_amount) }}</del>
                                    @else
                                        <h5 class="mb-0 w-50 text-lg-end text-sm-start"> <span
                                                class="fw-normal">{{ Currency::format($billingItem->service_amount ) }}
                                                *
                                                {{ $billingItem->quantity }} = </span>
                                            {{ Currency::format($billingItem->total_amount * $billingItem->quantity) }}
                                        </h5>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @php
                        $service_total_amount = 0; // Initialize outside the loop
                        $total_tax = 0;

                    @endphp
                    @if ($appointment->patientEncounter !== null)
                        @foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $item)
                            @php
                                $service_total_amount += $item->total_amount; // Sum up service amounts
                            @endphp
                        @endforeach
                    @endif

                    <ul class="list-unstyled pt-4 mb-0">
                        <?php
                        $transaction = $appointment->appointmenttransaction ? $appointment->appointmenttransaction : null;


                        $total_amount = 0;
                        $discount_amount = 0;
                        if ($appointment->patientEncounter !== null) {
                            $transaction = optional($appointment->patientEncounter)->billingrecord ? optional($appointment->patientEncounter)->billingrecord : null;
                            if ($transaction['final_discount_type'] == 'percentage') {
                                $discount_amount = $service_total_amount * ($transaction['final_discount_value'] / 100);
                            } else {
                                $discount_amount = $transaction['final_discount_value'];
                            }
                            if ($transaction != null) {
                                foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $billingItem) {
                                    $total_amount += $billingItem->total_amount;
                                }

                                 $tax = json_decode(optional($transaction)->tax_percentage, true);
                                //$tax = json_decode(optional($transaction)->tax_data, true);


                            }
                            $sub_total = $service_total_amount - $discount_amount;
                        } else {
                            $sub_total = $appointment->service_amount;
                        }

                        if ($appointment->appointmenttransaction == null) {
                            $tax = Modules\Tax\Models\Tax::active()->whereNull('module_type')->orWhere('module_type', 'services')->where('status', 1)->get();

                        }

                        ?>

                        @if ($appointment->appointmenttransaction == null)
                            @php
                                $pending_amount =
                                    ($appointment->total_amount * $appointment->clinicservice->advance_payment_amount) /
                                    100;
                            @endphp


                            @foreach ($tax as $t)

                                @if ($t['type'] == 'percent')
                                    <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                        <span>{{ $t['title'] }} ({{ $t['value'] }}%)</span>
                                        <?php
                                        $tax_amount = ($sub_total * $t['value']) / 100;
                                        if ($sub_total > 0) {
                                            $tax_amount = ($sub_total * $t['value']) / 100;
                                        }
                                        $total_tax += $tax_amount;
                                        ?>
                                        <span class="text-primary">{{ Currency::format($tax_amount) }}</span>
                                    </li>
                                @elseif($t['type'] == 'fixed')
                                    @php $total_tax += $t['value']; @endphp
                                    <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                        <span>{{ $t['title'] }}</span>
                                        <span class="text-primary">{{ Currency::format($t['value']) }}</span>
                                    </li>
                                @endif
                            @endforeach
                        @endif



                        @if ($transaction !== null)
                            @if ($appointment->patientEncounter !== null)
                                @php
                                    $tempServiceTotalAmount = $service_total_amount ;
                                @endphp
                                <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span>{{ __('messages.total') }}

                                    </span>

                                    <span class="text-dark">{{ Currency::format($tempServiceTotalAmount) ?? '--' }}</span>
                                </li>
                                <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span>{{ __('messages.discount') }}
                                        ( <span class="text-primary">
                                            @if ($transaction->final_discount_type === 'percentage')
                                                {{ $transaction->final_discount_value ?? '--' }}%
                                            @else
                                                {{ Currency::format($transaction->final_discount_value) ?? '--' }}
                                            @endif
                                        </span>)
                                    </span>
                                    <span class="text-dark">- {{ Currency::format($discount_amount) ?? '--' }}</span>
                                </li>

                                <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span>{{ __('messages.sub_total') }}</span>
                                    @php
                                        $tempSubTotal = $sub_total ;
                                    @endphp
                                    <span class="text-dark">{{ Currency::format($tempSubTotal) ?? '--' }}</span>
                                </li>
                            @endif

                            @php
                                  $tax = json_decode(optional($transaction)->tax_data, true)
    ?: json_decode(optional($transaction)->tax_percentage, true)
    ?: [];
                            @endphp


                            @foreach ($tax as $t)
                                @if ($t['type'] == 'percent')
                                    <?php
                                    $tax_amount = ($sub_total * $t['value']) / 100;
                                    if ($sub_total > 0) {
                                        $tax_amount = ($sub_total * $t['value']) / 100;
                                    }
                                    $total_tax += $tax_amount;
                                    ?>
                                    <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                        <span>{{ $t['title'] }} ({{ $t['value'] }}%)</span>

                                        <span class="text-primary">{{ Currency::format($tax_amount) }}</span>

                                    </li>
                                @elseif($t['type'] == 'fixed')
                                    @php $total_tax += $t['value']; @endphp
                                    <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                        <span>{{ $t['title'] }}</span>
                                        <span class="text-primary">{{ Currency::format($t['value']) }}</span>
                                    </li>
                                @endif
                            @endforeach

                            <!-- <li class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                                                                                                                                                                                                                                                                                                                                                                                                        <span>{{ __('messages.discount') }}
                                                                                                                                                                                                                                                                                                                                                                                                                       ( <span class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                                        @if ($transaction->discount_type === 'percentage')
    {{ $transaction->discount_value ?? '--' }}%
@else
    {{ Currency::format($transaction->discount_value) ?? '--' }}
    @endif
                                                                                                                                                                                                                                                                                                                                                                                        </span>)
                                                                                                                                                                                                                                                                                                                                                                                                                            </span>
                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="text-dark">{{ Currency::format($transaction->discount_amount) ?? '--' }}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                    </li> -->
                        @endif

                        @php
                            $grand_total = $appointment->patientEncounter
                                ? $service_total_amount + $total_tax - $discount_amount
                                : $appointment->total_amount;
                        @endphp

                        <li class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-2">
                            <h5 class="mb-0">{{ __('appointment.total') }}</h5>
                            @php

                            @endphp
                            @if ($appointment->patientEncounter !== null)
                                <h5 class="mb-0">{{ Currency::format($grand_total) }}</h5>
                            @else
                                <h5 class="mb-0">{{ Currency::format($grand_total) }}</h5>
                            @endif
                        </li>
                    </ul>


                    @if (optional($appointment->appointmenttransaction)->advance_payment_status == 1)
                        @php
                            $remaining_payable_amount = $grand_total - $appointment->advance_paid_amount;
                        @endphp
                        <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                            <span>{{ __('service.advance_payment_amount') }}
                                ({{ $appointment->advance_payment_amount }}%)</span>
                            <span
                                class="text-dark">{{ Currency::format($appointment->advance_paid_amount) ?? '--' }}</span>
                        </li>
                        @if (optional($appointment->appointmenttransaction)->payment_status != 1 && $appointment->status != 'cancelled')
                            <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                                <span>{{ __('service.remaining_amount') }}<span
                                        class="text-capitalize badge bg-warning p-2">{{ __('appointment.pending') }}</span></span></span>
                                <span class="text-dark">{{ Currency::format($remaining_payable_amount) ?? '--' }}</span>
                            </li>
                        @endif
                        @if (optional($appointment->appointmenttransaction)->payment_status == 1)
                            @if (optional($appointment->appointmenttransaction)->payment_status != 1 && $appointment->status != 'cancelled')
                                <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                                    <span>{{ __('service.remaining_amount') }}<span
                                            class="text-capitalize badge bg-warning p-2">{{ __('appointment.pending') }}</span></span></span>
                                    <span
                                        class="text-dark">{{ Currency::format($remaining_payable_amount) ?? '--' }}</span>
                                </li>
                            @endif
                            @if (optional($appointment->appointmenttransaction)->advance_payment_status == 1 && $appointment->status == 'checkout')
                                <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                                    <span>{{ __('service.remaining_amount') }}<span
                                            class="text-capitalize badge bg-success p-2">{{ __('appointment.paid') }}</span></span>
                                    <span
                                        class="text-dark">{{ Currency::format($remaining_payable_amount) ?? '--' }}</span>
                                </li>
                            @endif
                        @endif

                        @if (optional($appointment->appointmenttransaction)->advance_payment_status == 1 && $appointment->status == 'cancelled')
                            <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                                <span>{{ __('service.refund_amount') }}</span>
                                <span
                                    class="text-dark">{{ Currency::format($appointment->advance_paid_amount) ?? '--' }}</span>
                            </li>
                        @endif

                        @if ($appointment->appointmenttransaction == null && $appointment->clinicservice->is_enable_advance_payment == 1)
                            <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                                <span>{{ __('service.remaining_amount') }}<span
                                        class="text-capitalize badge bg-success p-2">{{ __('appointment.paid') }}</span></span>
                                <span class="text-dark">{{ Currency::format() }}</span>
                            </li>
                        @endif
                    @endif


                    @if ($appointment->appointmenttransaction == null)
                        <li class="d-flex align-items-center justify-content-between pb-2 mb-2">
                            <span>{{ __('appointment.pending_advance_payment_amount') }}</span>
                            <span class="text-dark">{{ Currency::format($pending_amount) ?? '--' }}</span>
                        </li>
                    @endif
                </div>
            </div>

        </div>

    @endsection

    @push('after-styles')
        <style>
            .detail-box {
                padding: 0.625rem 0.813rem;
            }
        </style>
        <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
    @endpush

    @push('after-scripts')
        <script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
        <script src="{{ mix('modules/appointment/script.js') }}"></script>
        <script src="{{ asset('js/form-offcanvas/index.js') }}" defer></script>
    @endpush
