import * as CookieConsent from "vanilla-cookieconsent";

const id = '';

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
                        window['ga-disable-${id}'] = true;
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
                    title: 'We use cookies',
                    description: 'Cookie modal description',
                    acceptAllBtn: 'Accept all',
                    acceptNecessaryBtn: 'Reject all',
                    showPreferencesBtn: 'Manage Individual preferences'
                },
                preferencesModal: {
                    title: 'Manage cookie preferences',
                    acceptAllBtn: 'Accept all',
                    acceptNecessaryBtn: 'Reject all',
                    savePreferencesBtn: 'Accept current selection',
                    closeIconLabel: 'Close modal',
                    sections: [
                        {
                            title: 'Somebody said ... cookies?',
                            description: 'I want one!'
                        },
                        {
                            title: 'Strictly Necessary cookies',
                            description: 'These cookies are essential for the proper functioning of the website and cannot be disabled.',

                            //this field will generate a toggle linked to the 'necessary' category
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Performance and Analytics',
                            description: 'These cookies collect information about how you use our website. All of the data is anonymized and cannot be used to identify you.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'More information',
                            description: 'For any queries in relation to my policy on cookies and your choices, please <a href="#contact-page">contact us</a>'
                        }
                    ]
                }
            }
        }
    }
});
