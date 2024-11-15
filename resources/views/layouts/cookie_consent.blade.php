@php
    // $settings = DB::table('settings')->Where('created_by', '=', 1)->get()->pluck('value', 'name');
    $settings = Utility::settings();
    


@endphp

<link rel='stylesheet' href='{{asset('assets/css/cookieconsent.css')}}' media="screen" />
<script src="{{ asset('assets/js/cookieconsent.js') }}"></script>
<script>
    let language_code = document.documentElement.getAttribute('lang');
    let languages = {};
    languages[language_code] = {
        consent_modal: {
            title: 'hello',
            description: 'description',
            primary_btn: {
                text: 'primary_btn text',
                role: 'accept_all'
            },
            secondary_btn: {
                        text: 'secondary_btn text',
                        role: 'accept_necessary'
                    }
                },
                settings_modal: {
                    title: 'settings_modal',
                    save_settings_btn: 'save_settings_btn',
                    accept_all_btn: 'accept_all_btn',
                    reject_all_btn: 'reject_all_btn',
                    close_btn_label: 'close_btn_label',
                    blocks: [{
                            title: 'block title',
                            description: 'block description'
                        },

                        {
                            title: 'title',
                            description: 'description',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: false
                            }
                        },
                    ]
                }
            };
            </script>
        <script>
            function setCookie(cname, cvalue, exdays) {
                const d = new Date();
                d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }

            function getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }


            // obtain plugin
            var cc = initCookieConsent();
            // run plugin with your configuration
            cc.run({
                current_lang: 'en',
                autoclear_cookies: true, // default: false
                page_scripts: true,
                // ...
                gui_options: {
                    consent_modal: {
                        layout: 'cloud', // box/cloud/bar
                        position: 'bottom center', // bottom/middle/top + left/right/center
                        transition: 'slide', // zoom/slide
                        swap_buttons: false // enable to invert buttons
                    },
                    settings_modal: {
                        layout: 'box', // box/bar
                        // position: 'left',           // left/right
                        transition: 'slide' // zoom/slide
                    }
                },

                onChange: function(cookie, changed_preferences) {},
                onAccept: function(cookie) {
                    if (!getCookie('cookie_consent_logged')) {
                        var cookie = cookie.level;
                        $.ajax({
                            url: '{{ route('cookie-consent') }}',
                            datType: 'json',
                            data: {
                                cookie: cookie,
                            },
                        })
                        setCookie('cookie_consent_logged', '1', 182, '/');
                    }
                },

                languages: {
                    'en': {
                        consent_modal: {

                            title: '{{ !empty($settings["cookie_title"]) ? $settings["cookie_title"] : "We use cookies" }}',
                            description:'{{ !empty($settings["cookie_description"]) ? $settings["cookie_description"] : "We use cookies" }} <button type="button" data-cc="c-settings" class="cc-link">Let me choose</button>',
                            primary_btn: {
                                text: 'Accept all',
                                role: 'accept_all' // 'accept_selected' or 'accept_all'
                            },
                            secondary_btn: {
                                text: 'Reject all',
                                role: 'accept_necessary' // 'settings' or 'accept_necessary'
                            },
                        },
                        settings_modal: {
                            title: 'Cookie preferences',
                            save_settings_btn: 'Save settings',
                            accept_all_btn: 'Accept all',
                            reject_all_btn: 'Reject all',
                            close_btn_label: 'Close',
                            cookie_table_headers: [{
                                col1: 'Name'
                            },
                            {
                                col2: 'Domain'
                                },
                                {
                                    col3: 'Expiration'
                                },
                                {
                                    col4: 'Description'
                                }
                            ],
                            blocks: [{
                                title: 'Cookie usage 📢',
                                description: 'I use cookies to ensure the basic functionalities of the website and to enhance your online experience. You can choose for each category to opt-in/out whenever you want. For more details relative to cookies and other sensitive data, please read the full <a href="#" class="cc-link">privacy policy</a>.'
                            }, {
                                title: 'Strictly necessary cookies',
                                description:'{{ !empty($settings["strictly_cookie_description"]) ? $settings["strictly_cookie_description"] : "We use cookies" }}',
                                toggle: {
                                    value: 'necessary',
                                    enabled: true,
                                    readonly: true // cookie categories with readonly=true are all treated as "necessary cookies"
                                }
                            }, {
                                title: 'More information',
                                description: '{{ !empty($settings["more_information_description"]) ? $settings["more_information_description"] : "We use cookies" }}, please <a class="cc-link" href="{{ !empty($settings["contactus_url"]) ? $settings["contactus_url"] : "#" }}" target="_blank">contact us</a>.',
                            }]
                        }
                    }
                }

            });
        </script>
