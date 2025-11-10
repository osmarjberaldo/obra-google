import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LanguageSelector } from '@/components/LanguageSelector';
import { Mail, HardHat } from 'lucide-react';
import { toast } from 'sonner';
import constructionHero from '@/assets/construction-hero.jpg';

const ForgotPassword = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');

  const handleForgotPassword = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Simulação de envio de email de recuperação de senha
    if (email) {
      toast.success(t('password_reset_sent'));
      // Redirecionar para login após envio
      setTimeout(() => {
        navigate('/login');
      }, 2000);
    } else {
      toast.error(t('enter_valid_email'));
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* Left side - Image */}
      <div className="hidden lg:flex lg:w-1/2 relative">
        <img
          src={constructionHero}
          alt="Construction site"
          className="absolute inset-0 w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent" />
      </div>

      {/* Right side - Form */}
      <div className="flex-1 flex items-center justify-center p-8 bg-background">
        <div className="w-full max-w-md space-y-8">
          <div className="absolute top-8 right-8">
            <LanguageSelector />
          </div>

          <div className="text-center">
            <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 mb-4">
              <HardHat className="h-8 w-8 text-primary" />
            </div>
            <h1 className="text-2xl font-bold text-foreground">Gestão de Obra Fácil</h1>
          </div>

          <div className="space-y-2 text-center">
            <h2 className="text-3xl font-bold text-foreground">{t('forgot_password_title')}</h2>
            <p className="text-muted-foreground">{t('enter_email_reset')}</p>
          </div>

          <form onSubmit={handleForgotPassword} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="email">{t('email')}</Label>
              <div className="relative">
                <Mail className="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                <Input
                  id="email"
                  type="email"
                  placeholder="seuemail@exemplo.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="pl-10"
                  required
                />
              </div>
            </div>

            <Button type="submit" className="w-full" size="lg">
              {t('send_reset_link')}
            </Button>
          </form>

          <div className="text-center text-sm">
            <p className="text-muted-foreground">
              {t('remember_password')}{' '}
              <Link to="/login" className="text-primary hover:underline font-medium">
                {t('back_to_login')}
              </Link>
            </p>
          </div>

          <div className="text-center text-sm text-muted-foreground">
            © 2024 Gestão de Obra Fácil. Todos os direitos reservados.
          </div>
        </div>
      </div>
    </div>
  );
};

export default ForgotPassword;