import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { LanguageSelector } from '@/components/LanguageSelector';
import { HardHat, Phone, Eye, EyeOff, Lock } from 'lucide-react';
import { toast } from 'sonner';
import constructionSignup from '@/assets/construction-signup.jpg';
import PhoneInput from 'react-phone-number-input';
import 'react-phone-number-input/style.css';
import InputMask from 'react-input-mask';
import * as cpfCnpjValidator from 'cpf-cnpj-validator';

// Estilos personalizados para o PhoneInput
const phoneInputStyle = `
  .PhoneInput {
    position: relative;
  }
  
  .PhoneInputInput {
    width: 100%;
    height: 40px;
    padding-left: 2.5rem;
    border-radius: 0.375rem;
    border: 1px solid hsl(var(--input));
    background-color: hsl(var(--background));
    font-size: 0.875rem;
    line-height: 1.25rem;
    transition: all 0.2s ease-in-out;
  }
  
  .PhoneInputInput:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    ring: 2px solid hsl(var(--ring));
    ring-offset: 2px;
  }
  
  .PhoneInputCountry {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
  }
  
  .PhoneInputCountrySelect {
    display: none;
  }
`;

const Signup = () => {
  const { t, i18n } = useTranslation();
  const currentLanguage = i18n.language;
  const showDocumentMask = currentLanguage === 'pt-BR';
  const navigate = useNavigate();
  const [accountType, setAccountType] = useState<'individual' | 'company'>('individual');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [cpf, setCpf] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [agreedToTerms, setAgreedToTerms] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const validatePassword = (password: string) => {
    const requirements = [
      { id: 'length', text: t('password_too_short'), met: password.length >= 8 },
      { id: 'uppercase', text: t('password_missing_uppercase'), met: /[A-Z]/.test(password) },
      { id: 'symbol', text: t('password_missing_symbol'), met: /[!@#$%^&*()]/.test(password) }
    ];
    
    return requirements;
  };

  const validateDocument = (document: string, type: 'individual' | 'company') => {
    // Se não for português, não validar documento
    if (!showDocumentMask) return true;
    
    const cleanDocument = document.replace(/\D/g, '');
    
    if (type === 'individual') {
      return cpfCnpjValidator.cpf.isValid(cleanDocument);
    } else {
      return cpfCnpjValidator.cnpj.isValid(cleanDocument);
    }
  };

  const handleSignup = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validar documento apenas se for português
    if (showDocumentMask) {
      const isDocumentValid = validateDocument(cpf, accountType);
      if (!isDocumentValid) {
        toast.error(accountType === 'individual' 
          ? 'CPF inválido' 
          : 'CNPJ inválido');
        return;
      }
    }
    
    // Validar senha
    const passwordErrors = validatePassword(password);
    if (passwordErrors.length > 0) {
      toast.error(passwordErrors.join(', '));
      return;
    }
    
    if (!agreedToTerms) {
      toast.error('Você precisa concordar com os termos');
      return;
    }

    toast.success('Conta criada com sucesso!');
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex">
      {/* Left side - Hero */}
      <div className="hidden lg:flex lg:w-2/5 relative bg-gradient-to-br from-slate-900 to-slate-800 p-12 flex-col justify-between text-white">
        <div className="absolute inset-0 opacity-20">
          <img
            src={constructionSignup}
            alt="Construction"
            className="w-full h-full object-cover"
          />
        </div>
        
        <div className="relative z-10">
          <div className="flex items-center gap-2 mb-8">
            <HardHat className="h-8 w-8 text-primary" />
            <span className="text-xl font-bold">Gestão de Obra Fácil</span>
          </div>
          
          <h1 className="text-4xl font-bold mb-4">{t('build_future')}</h1>
          <p className="text-lg text-slate-300">{t('join_professionals')}</p>
        </div>

        <div className="relative z-10 text-sm text-slate-400">
          © 2024 Gestão de Obra Fácil
        </div>
      </div>

      {/* Right side - Form */}
      <div className="flex-1 flex items-center justify-center p-8 bg-background overflow-y-auto">
        <div className="w-full max-w-md space-y-6 py-8">
          <div className="absolute top-8 right-8">
            <LanguageSelector />
          </div>

          <div className="space-y-2 text-center">
            <h2 className="text-3xl font-bold text-foreground">{t('create_account')}</h2>
            <p className="text-muted-foreground">{t('start_managing')}</p>
          </div>

          {/* Account Type Toggle */}
          <div className="flex gap-2 p-1 bg-muted rounded-lg">
            <button
              type="button"
              onClick={() => setAccountType('individual')}
              className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors ${
                accountType === 'individual'
                  ? 'bg-background text-foreground shadow-sm'
                  : 'text-muted-foreground hover:text-foreground'
              }`}
            >
              {t('individual')}
            </button>
            <button
              type="button"
              onClick={() => setAccountType('company')}
              className={`flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors ${
                accountType === 'company'
                  ? 'bg-background text-foreground shadow-sm'
                  : 'text-muted-foreground hover:text-foreground'
              }`}
            >
              {t('company')}
            </button>
          </div>

          <form onSubmit={handleSignup} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="name">
                {accountType === 'individual' ? t('full_name') : t('corporate_name')}
              </Label>
              <Input
                id="name"
                type="text"
                placeholder={accountType === 'individual' ? t('full_name') : t('corporate_name')}
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="email">{t('email')}</Label>
              <Input
                id="email"
                type="email"
                placeholder="exemplo@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="cpf">
                {accountType === 'individual' 
                  ? (showDocumentMask ? t('cpf') : t('document_number'))
                  : (showDocumentMask ? t('cnpj') : t('document_number'))}
              </Label>
              {showDocumentMask ? (
                <InputMask
                  id="cpf"
                  mask={accountType === 'individual' ? "999.999.999-99" : "99.999.999/9999-99"}
                  placeholder={accountType === 'individual' ? "000.000.000-00" : "00.000.000/0000-00"}
                  value={cpf}
                  onChange={(e) => setCpf(e.target.value)}
                  className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                />
              ) : (
                <Input
                  id="cpf"
                  type="text"
                  placeholder={t('document_number')}
                  value={cpf}
                  onChange={(e) => setCpf(e.target.value)}
                  required
                />
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="phone">{t('phone')}</Label>
              <PhoneInput
                id="phone"
                international
                defaultCountry="BR"
                value={phone}
                onChange={setPhone}
                placeholder={t('enter_phone')}
                countryCallingCodeEditable={false}
                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">{t('password')}</Label>
              <div className="relative">
                <Lock className="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                <Input
                  id="password"
                  type={showPassword ? "text" : "password"}
                  placeholder={t('create_strong_password')}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="pl-10 pr-10"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-3 text-muted-foreground hover:text-foreground"
                >
                  {showPassword ? (
                    <EyeOff className="h-5 w-5" />
                  ) : (
                    <Eye className="h-5 w-5" />
                  )}
                </button>
              </div>
              
              {/* Lista de requisitos de senha */}
              <div className="space-y-1">
                {validatePassword(password).map((req) => (
                  <div key={req.id} className="flex items-center text-sm">
                    {req.met ? (
                      <span className="text-green-500 mr-2">✓</span>
                    ) : (
                      <span className="text-red-500 mr-2">✗</span>
                    )}
                    <span className={req.met ? "text-green-500" : "text-red-500"}>
                      {req.text}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            <div className="flex items-start space-x-2">
              <Checkbox
                id="terms"
                checked={agreedToTerms}
                onCheckedChange={(checked) => setAgreedToTerms(checked as boolean)}
              />
              <label
                htmlFor="terms"
                className="text-sm text-muted-foreground leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                {t('agree_terms')}{' '}
                <Link to="https://gestaodeobrafacil.com/termos.php" className="text-primary hover:underline">
                  {t('terms_of_service')}
                </Link>{' '}
                {t('and')}{' '}
                <Link to="https://gestaodeobrafacil.com/privacidade.php" className="text-primary hover:underline">
                  {t('privacy_policy')}
                </Link>
              </label>
            </div>

            <Button type="submit" className="w-full" size="lg">
              {t('create_account_button')}
            </Button>
          </form>

          <div className="text-center text-sm text-muted-foreground">
            {t('already_have_account')}{' '}
            <Link to="/login" className="text-primary hover:underline font-medium">
              {t('login_here')}
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Signup;
