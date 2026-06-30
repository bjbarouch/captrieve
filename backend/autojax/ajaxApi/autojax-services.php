<?php

namespace AutoJax;

/**
 * @file autojax-services.php
 *
 * built-in autojax service definitions
 *
 * defines the ajaxApi permission matrix for all services that ship with autojax
 * cross-origin eligibility is derived from the matrix (an action with a false crud value), not declared here
 *
 * this file is owned by autojax and will be replaced on reinstall -- do not edit it
 * to register app-specific services, declare them in autojax.json ajaxApi section
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

/**
 * returns the service permission matrix for all built-in autojax services
 *
 * permission values:
 *   null  -- action is not part of the api
 *   false -- action is part of the api. authentication is not required
 *   true  -- action is part of the api. authentication is required
 *
 * cross-origin eligibility is derived from these values (a false crud value), not declared
 *
 * @return array{services: array<string, object>}
 */
function autoJaxServiceConfig() {
    return [
        "services"    => [
            "clientLog"       => (object)[
                "subService" => null,
                "create"     => false,  // client code can error in public areas – DoS possible
                "read"       => null,
                "update"     => null,
                "delete"     => null
            ],
            "registerNewUser" => (object)[
                "subService" => null,
                "create"     => false,  // auth cannot be required for self-registration – DoS possible
                "read"       => null,
                "update"     => null,
                "delete"     => null
            ],
            "confirmEmail"    => (object)[
                "subService" => null,
                // consumes the email_confirm token from a registration link. unauthenticated by definition
                "create"     => null,
                "read"       => null,
                "update"     => false,
                "delete"     => null
            ],
            "session"         => (object)[
                "subService" => null,
                // the whole session lifecycle on one resource: create = login, read = cross-page exchange,
                // update = keep-alive, delete = logout. session.php dispatches on the action. each action carries
                // its own auth requirement below, so public and authenticated paths coexist on one service
                "create"     => false,  // login: auth cannot be required to log in – DoS possible
                "read"       => false,  // cross-page exchange: the credential is the httpOnly cookie, not a jwt
                "update"     => true,   // keep-alive: extend an already-authenticated session
                "delete"     => true    // logout: revoke the session server-side (stamp lastRevocation)
            ],
            "password"        => (object)[
                "subService" => null,
                "create"     => null,
                "read"       => false,  // forgot password: request a reset link (unauthenticated by definition)
                "update"     => false,  // reset password: consume reset JWT and set new password
                "delete"     => null
            ],
            "admin"           => (object)[
                "subService" => null,
                // all admin actions require an authenticated session with isAdmin = 1
                // ajaxPort enforces session auth. admin.php additionally enforces isAdmin
                "create"     => true,
                "read"       => true,
                "update"     => true,
                "delete"     => true
            ]
        ]
    ];
}
