import { BrowserRouter, Routes, Route, Link } from 'react-router-dom';
import Home from './Home';
import About from './About';
import Contact from './Contact';
import './App.css';

export default function App() {
  return (
    <BrowserRouter>
      <header className="navbar-header">
        <div className="logo">MyWebsite</div>
        <nav className="nav-links">
          <NavLink to="/" className={({ isActive }) => isActive ? "nav-item active" : "nav-item"}>Home</NavLink>
          <NavLink to="/about" className={({ isActive }) => isActive ? "nav-item active" : "nav-item"}>About</NavLink>
          <NavLink to="/contact" className={({ isActive }) => isActive ? "nav-item active" : "nav-item"}>Contact</NavLink>
        </nav>
      </header>

      <main className="main-content">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/contact" element={<Contact />} />
        </Routes>
      </main>
    </BrowserRouter>
  );
}
