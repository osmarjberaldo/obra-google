import { Navigate, Outlet, useLocation } from "react-router-dom";

const isUserAuthenticated = () => {
  try {
    const isAuth = localStorage.getItem("isAuthenticated");
    const token = localStorage.getItem("userToken");
    return isAuth === "true" || !!token;
  } catch {
    return false;
  }
};

export const ProtectedRoute = () => {
  const location = useLocation();
  const authed = isUserAuthenticated();

  if (!authed) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  return <Outlet />;
};

export default ProtectedRoute;