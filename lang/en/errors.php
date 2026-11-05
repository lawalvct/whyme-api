<?php

// Server-side messages for API error codes. Clients translate by code using
// @whyme/i18n; these are the fallback text and are used in emails/SMS/push.

return [
    'network_error' => "No connection. Your work is saved and will send when you're back online.",
    'server_error' => 'Something went wrong on our side. Please try again.',
    'validation_failed' => 'Please check the highlighted fields.',
    'unauthenticated' => 'Please sign in to continue.',
    'forbidden' => "You don't have permission to do that.",
    'not_found' => "We couldn't find that.",
    'too_many_requests' => 'Too many attempts. Please wait a moment and try again.',
    'conflict' => 'This was changed elsewhere. Review the latest version.',
    'idempotency_mismatch' => 'This action was already sent with different details. Please start it again.',
    'idempotency_key_invalid' => 'The request ID is not valid.',
    'request_in_progress' => 'This action is already being processed. Please wait.',
    'invalid_credentials' => 'Phone number or password is incorrect.',
    'otp_invalid' => 'That code is not correct.',
    'otp_expired' => 'That code has expired. Request a new one.',
    'phone_invalid' => 'Enter a valid Nigerian phone number.',
    'phone_taken' => 'An account already exists for this phone number. Sign in instead.',
    'account_suspended' => 'This account is suspended. Contact WHY ME support.',
    'terms_required' => 'Please accept the current seller terms to continue.',
    'app_update_required' => 'Please update WHY ME to continue.',
];
