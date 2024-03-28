import "@tabler/core/dist/js/tabler";
import * as echarts from "echarts";
import * as CookieConsent from "vanilla-cookieconsent";

window.echarts = echarts;

// Cookie consent banner
window.addEventListener("DOMContentLoaded", () => {
    CookieConsent.run({
        categories: {
            analytics: {
                services: {
                    ga: {
                        label: 'Google Analytics',
                        onAccept: () => {
                            var gaScript = document.createElement('script');
                            gaScript.async = true;
                            gaScript.src = `https://www.googletagmanager.com/gtag/js?id=${window.google_analytics_id}`;
                            document.head.appendChild(gaScript);

                            var gaConfigScript = document.createElement('script');
                            gaConfigScript.textContent = `
                                window.dataLayer = window.dataLayer || [];
                                function gtag(){dataLayer.push(arguments);}
                                gtag('js', new Date());
                                gtag('config', '${window.google_analytics_id}');
                            `;
                            document.head.appendChild(gaConfigScript);
                        },
                        onReject: () => {
                            window[`ga-disable-${window.google_analytics_id}`] = true;
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
});
