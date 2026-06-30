/**
 * @file autojax-config.js
 *
 * client-side configuration for autojax, populated from autojax.json
 *
 * all values except logout are read from autojax.json.
 * to override logout behavior, reassign autoJaxConfig.logout in your app code
 * after importing this module.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

import { readAutoJaxJson }                            from "./util/readAutoJaxJson.js";
import { getPasswordMinLength, setPasswordMinLength } from "./util/util.js";

const
    json    = await readAutoJaxJson(),
    // autojax's own directory URL, derived from this module's own location rather than configured.
    // this module is installed at autojax/autojax-config.js, so its directory is autojax/ itself.
    // nothing in autojax.json is needed to find it, and it survives any move of autojax/.
    // its only present use is locating autojax's bundled assets below. kept exposed for future need
    coreUrl = new URL(".", import.meta.url).href.replace(/\/$/, "");
// fold the configured minimum into util.js so its isValidPassword mirrors the server, and expose the
// clamped value below for app code that builds its own registration UI
setPasswordMinLength(json.password?.minLength);

export const autoJaxConfig = {                    // completely set during installation
    ajaxUrl           : json.ajaxUrl,             // url for reaching autojax/ajaxApi/ajaxPort.php
    coreUrl           : coreUrl,                  // url of autojax/ itself, for autojax use only
    assetsUrl         : `${coreUrl}/assets`,      // ignore this, for autojax use only
    loginUrl          : json.loginUrl,            // for when autojax needs to redirect a user to your app's login page
    passwordMinLength : getPasswordMinLength(),   // defaults to 12
    user              : null,                     // null unless and until a user logs in, then it's the user info object,
                                                  // containing autoJaxId, displayName, email, and isAdmin for your use
    /**
     * the app's logout handler. it runs as the last step of session teardown: an explicit logout() (sessionMgmt.js), an idle
     * timeout, or a 401. on an explicit logout the session is revoked on the server before this is active in the UI to
     * redirect the user to the login page or elsewhere.
     * you can also do any session cleanup that makes sense for your app.
     *
     * @returns {void}
     */
    logout            : function logout() {
        // do some session cleanup here if that makes sense in your app
        // then go to the login page, or elsewhere if you prefer
        location.href = json.loginUrl;
    }
};
