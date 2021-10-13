import i18next from 'i18next';
import en from './../locales/en.json';
import ja from './../locales/ja.json';
import bn from './../locales/bn.json';

i18next.init({
	resources: {
		en: {
			translation: en
		},
		ja: {
			translation: ja
		},
		bn: {
			translation: bn
		}
	},
	lng: defaultLocale,
	fallbackLng: fallbackLocale,
	interpolation: {
		escapeValue: false
	}
});

export default i18next;