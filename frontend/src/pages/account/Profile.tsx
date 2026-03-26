import { useEffect, useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { getProfile, updateProfile, updatePassword } from '../../api';
import { useToast } from '../../components/Toast';
import { useAuth } from '../../context/AuthContext';

export default function Profile() {
  const { refresh } = useAuth();
  const addToast    = useToast();

  const [profile, setProfile]   = useState<Record<string, any>>({});
  const [pwForm, setPwForm]     = useState<{ old_password: string; new_password: string; confirm: string }>({ old_password: '', new_password: '', confirm: '' });
  const [saving, setSaving]     = useState(false);
  const [savingPw, setSavingPw] = useState(false);

  useEffect(() => {
    getProfile().then(r => setProfile(r.data.data || {})).catch(() => {});
  }, []);

  const setP = (k: string, v: string) => setProfile(p => ({ ...p, [k]: v }));
  const setPw = (k: string, v: string) => setPwForm(p => ({ ...p, [k]: v }));

  const saveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const r = await updateProfile({ name: profile.name, mobile: profile.mobile });
      if (r.data.status) { addToast('Profile updated!', 'success'); refresh(); }
      else addToast(r.data.message, 'error');
    } catch { addToast('Failed to update profile', 'error'); }
    finally  { setSaving(false); }
  };

  const savePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (pwForm.new_password !== pwForm.confirm) { addToast('Passwords do not match', 'error'); return; }
    setSavingPw(true);
    try {
      const r = await updatePassword({ old_password: pwForm.old_password, new_password: pwForm.new_password });
      if (r.data.status) { addToast('Password changed!', 'success'); setPwForm({ old_password: '', new_password: '', confirm: '' }); }
      else addToast(r.data.message, 'error');
    } catch { addToast('Failed to change password', 'error'); }
    finally  { setSavingPw(false); }
  };

  return (
    <AccountLayout>
      <h1 className="font-display font-bold text-2xl text-gray-900 mb-6">My Profile</h1>
      <div className="space-y-5">
        {/* Profile form */}
        <div className="card p-5">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Personal Information</h2>
          <form onSubmit={saveProfile} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div><label className="label">Full Name</label><input value={profile.name || ''} onChange={e => setP('name', e.target.value)} className="input" placeholder="Your name"/></div>
              <div><label className="label">Email</label><input value={profile.email || ''} disabled className="input opacity-50 cursor-not-allowed"/></div>
              <div><label className="label">Mobile</label><input value={profile.mobile || ''} onChange={e => setP('mobile', e.target.value)} className="input" placeholder="Mobile number" type="tel"/></div>
            </div>
            <button type="submit" disabled={saving} className="btn-primary">{saving ? 'Saving…' : 'Save Changes'}</button>
          </form>
        </div>

        {/* Password form */}
        <div className="card p-5">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Change Password</h2>
          <form onSubmit={savePassword} className="space-y-4">
            <div><label className="label">Current Password</label><input type="password" value={pwForm.old_password} onChange={e => setPw('old_password', e.target.value)} className="input" placeholder="••••••••"/></div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div><label className="label">New Password</label><input type="password" value={pwForm.new_password} onChange={e => setPw('new_password', e.target.value)} className="input" placeholder="Min. 6 characters"/></div>
              <div><label className="label">Confirm Password</label><input type="password" value={pwForm.confirm} onChange={e => setPw('confirm', e.target.value)} className="input" placeholder="Repeat password"/></div>
            </div>
            <button type="submit" disabled={savingPw} className="btn-primary">{savingPw ? 'Updating…' : 'Update Password'}</button>
          </form>
        </div>
      </div>
    </AccountLayout>
  );
}
