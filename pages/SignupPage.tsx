
import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import type { Page } from '../App';
import LogoIcon from '../components/icons/LogoIcon';

interface SignupPageProps {
  onNavigate: (page: Page) => void;
}

type AccountType = 'individual' | 'legal_entity';

const SignupPage: React.FC<SignupPageProps> = ({ onNavigate }) => {
  const { t } = useI18n();
  const [accountType, setAccountType] = useState<AccountType>('individual');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // In a real app, this would handle registration.
    // For now, it just prevents form submission and logs to console.
    console.log('Account creation submitted.');
    alert('Funcionalidade de cadastro não implementada. Use as credenciais de teste na tela de login.');
    onNavigate('login');
  };

  return (
    <div className="flex min-h-screen">
      <div className="hidden lg:block lg:w-1/2 bg-slate-800 text-white p-12 flex flex-col justify-center relative">
          <div className="absolute inset-0 bg-cover bg-center" style={{backgroundImage: "url('https://picsum.photos/800/1200?random=2')", opacity: 0.2}}></div>
          <div className="relative z-10 flex flex-col justify-center h-full">
              <div className="flex items-center mb-4">
                  <LogoIcon className="h-8 w-8 mr-3" />
                  <h1 className="text-2xl font-bold">Gestão de Obra Fácil</h1>
              </div>
              <h2 className="text-4xl font-bold mt-8 leading-tight">Construa o futuro da sua gestão.</h2>
              <p className="mt-4 text-slate-300">Junte-se a milhares de profissionais que simplificam seus projetos conosco.</p>
          </div>
      </div>
      <div className="w-full lg:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white">
        <div className="w-full max-w-sm mx-auto">
          <h2 className="text-3xl font-bold text-slate-900 mb-2">{t('signup.create_account')}</h2>
          <p className="text-slate-600 mb-8">{t('signup.manage_projects_prompt')}</p>

          <div className="grid grid-cols-2 gap-2 mb-6 bg-slate-100 p-1 rounded-lg">
            <button
              onClick={() => setAccountType('individual')}
              className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${accountType === 'individual' ? 'bg-white shadow text-brand-orange-600' : 'text-slate-600 hover:bg-slate-200'}`}
            >
              {t('signup.individual')}
            </button>
            <button
              onClick={() => setAccountType('legal_entity')}
              className={`px-4 py-2 text-sm font-semibold rounded-md transition-colors ${accountType === 'legal_entity' ? 'bg-white shadow text-brand-orange-600' : 'text-slate-600 hover:bg-slate-200'}`}
            >
              {t('signup.legal_entity')}
            </button>
          </div>

          <form onSubmit={handleSubmit}>
            {accountType === 'individual' ? (
              <>
                <div className="mb-4">
                  <label className="block text-slate-700 text-sm font-bold mb-2">{t('signup.full_name')}</label>
                  <input type="text" placeholder={t('signup.full_name_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                </div>
                 <div className="mb-4">
                  <label className="block text-slate-700 text-sm font-bold mb-2">{t('login.email')}</label>
                  <input type="email" placeholder={t('login.email_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                </div>
                <div className="mb-4">
                  <label className="block text-slate-700 text-sm font-bold mb-2">{t('signup.cpf')}</label>
                  <input type="text" placeholder={t('signup.cpf_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                </div>
              </>
            ) : (
                 <>
                    <div className="mb-4">
                        <label className="block text-slate-700 text-sm font-bold mb-2">{t('signup.company_name')}</label>
                        <input type="text" placeholder={t('signup.company_name_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                    </div>
                    <div className="mb-4">
                        <label className="block text-slate-700 text-sm font-bold mb-2">{t('login.email')}</label>
                        <input type="email" placeholder={t('login.email_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                    </div>
                    <div className="mb-4">
                        <label className="block text-slate-700 text-sm font-bold mb-2">{t('signup.cnpj')}</label>
                        <input type="text" placeholder={t('signup.cnpj_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
                    </div>
                </>
            )}
             <div className="mb-4">
              <label className="block text-slate-700 text-sm font-bold mb-2">{t('login.password')}</label>
              <input type="password" placeholder={t('signup.create_password_placeholder')} className="w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500" required />
            </div>

            <div className="mb-6 flex items-start">
                <input type="checkbox" id="terms" className="mt-1 h-4 w-4 text-brand-orange-600 border-gray-300 rounded focus:ring-brand-orange-500" required />
                <label htmlFor="terms" className="ml-2 text-sm text-slate-600">
                    {t('signup.terms_agreement')}{' '}
                    <a href="#" className="font-semibold text-brand-orange-500 hover:underline">{t('signup.terms_of_service')}</a>{' '}
                    {t('signup.and')}{' '}
                    <a href="#" className="font-semibold text-brand-orange-500 hover:underline">{t('signup.privacy_policy')}</a>.
                </label>
            </div>
            
            <button
              type="submit"
              className="w-full bg-brand-yellow-500 hover:bg-brand-yellow-600 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-300"
            >
              {t('signup.create_account_button')}
            </button>
          </form>

          <div className="text-center mt-6">
            <p className="text-sm text-slate-600">{t('signup.already_have_account')}
                <button onClick={() => onNavigate('login')} className="font-semibold text-brand-orange-500 hover:text-brand-orange-600 ml-1">
                    {t('signup.login_link')}
                </button>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SignupPage;
