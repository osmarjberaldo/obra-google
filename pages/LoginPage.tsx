
import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import type { Page } from '../App';
import MailIcon from '../components/icons/MailIcon';
import LockIcon from '../components/icons/LockIcon';
import LogoIcon from '../components/icons/LogoIcon';

interface LoginPageProps {
  onLoginSuccess: () => void;
  onNavigate: (page: Page) => void;
}

const LoginPage: React.FC<LoginPageProps> = ({ onLoginSuccess, onNavigate }) => {
  const { t } = useI18n();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (email === 'test@test.com.br' && password === '123456') {
      onLoginSuccess();
    } else {
      setError('Credenciais inválidas. Tente novamente.');
    }
  };

  return (
    <div className="flex min-h-screen">
      <div className="hidden lg:block lg:w-1/2">
        <img
          src="https://picsum.photos/800/1200?random=1"
          alt="Construction Site"
          className="w-full h-full object-cover"
        />
      </div>
      <div className="w-full lg:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white">
        <div className="w-full max-w-sm mx-auto">
          <div className="flex items-center mb-6">
            <div className="bg-brand-orange-500 p-2 rounded-lg mr-3">
              <LogoIcon className="text-white h-6 w-6" />
            </div>
            <h1 className="text-2xl font-bold text-slate-800">Gestão de Obra Fácil</h1>
          </div>
          <h2 className="text-3xl font-bold text-slate-900 mb-2">{t('login.welcome_back')}</h2>
          <p className="text-slate-600 mb-8">{t('login.credentials_prompt')}</p>

          <form onSubmit={handleSubmit}>
            <div className="mb-4">
              <label className="block text-slate-700 text-sm font-bold mb-2" htmlFor="email">
                {t('login.email')}
              </label>
              <div className="relative">
                <MailIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                <input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder={t('login.email_placeholder')}
                  className="pl-10 w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500"
                  required
                />
              </div>
            </div>

            <div className="mb-6">
              <label className="block text-slate-700 text-sm font-bold mb-2" htmlFor="password">
                {t('login.password')}
              </label>
              <div className="relative">
                <LockIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                <input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder={t('login.password_placeholder')}
                  className="pl-10 w-full px-3 py-3 text-slate-700 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange-500"
                  required
                />
              </div>
              <div className="text-right mt-2">
                <a href="#" className="text-sm text-brand-orange-500 hover:text-brand-orange-600 font-semibold">
                  {t('login.forgot_password')}
                </a>
              </div>
            </div>
            
            {error && <p className="text-red-500 text-sm text-center mb-4">{error}</p>}

            <button
              type="submit"
              className="w-full bg-brand-orange-500 hover:bg-brand-orange-600 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-300"
            >
              {t('login.login_button')}
            </button>
          </form>

          <div className="text-center mt-8">
            <p className="text-sm text-slate-600">{t('login.no_account')}
                <button onClick={() => onNavigate('signup')} className="font-semibold text-brand-orange-500 hover:text-brand-orange-600 ml-1">
                    {t('login.signup_link')}
                </button>
            </p>
          </div>

          <p className="text-center text-xs text-slate-500 mt-12">{t('login.copyright')}</p>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
