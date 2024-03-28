import * as CookieConsent from "vanilla-cookieconsent";

const id = window.google_analytics_id;

CookieConsent.run({

    categories: {
        analytics: {
            services: {
                ga: {
                    label: 'Google Analytics',
                    onAccept: () => {
                        var gaScript = document.createElement('script');
                        gaScript.async = true;
                        gaScript.src = `https://www.googletagmanager.com/gtag/js?id=${id}`;
                        document.head.appendChild(gaScript);

                        var gaConfigScript = document.createElement('script');
                        gaConfigScript.textContent = `
                        window.dataLayer = window.dataLayer || [];
                        function gtag(){dataLayer.push(arguments);}
                        gtag('js', new Date());
                        gtag('config', '${id}');
                        `;
                        document.head.appendChild(gaConfigScript);
                    },
                    onReject: () => {
                        window[`ga-disable-${id}`] = true;
                    },
                    cookies: [
                        {
                            name: /^(_ga|_gid)/
                        }
                    ]
                },
            }
        }
    },

    language: {
        default: 'en',
        translations: {
            en: {
                consentModal: {
                    title: 'Welcome to the GLPI telemetry website',
                    description: "We use cookies to collect information about how you use our website. By clicking 'Accept', you agree to the use of cookies.",
                    acceptAllBtn: 'Accept',
                    acceptNecessaryBtn: 'Reject',
                    showPreferencesBtn: null
                }
            }
        }
    }
});
