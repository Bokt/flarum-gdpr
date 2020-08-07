import { extend } from 'flarum/extend';
import SettingsPage from 'flarum/components/SettingsPage';
import Button from 'flarum/components/Button';
import RequestDataModal from './components/RequestDataModal';

app.initializers.add(
    'bokt-gdpr',
    () => {
        extend(SettingsPage.prototype, 'accountItems', function (items) {
            items.add(
                'gdprExport',
                Button.component({
                    children: app.translator.trans('bokt-gdpr.forum.settings.export_data_button'),
                    className: 'Button',
                    onclick: () => app.modal.show(RequestDataModal),
                })
            );
        });
    }
);
