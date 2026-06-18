import React from 'react'
import { Link } from 'react-router-dom'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function Home() {
  return (
    <>
      <Header />
      <Nav />
      
      <div className="container mt-5">
        <div className="row">
          <div className="col-sm-4">
            <h2>About Me</h2>
            <h5>Photo of me:</h5>
            <div className="bg-secondary text-white text-center p-5 rounded">Fake Image</div>
            <p className="mt-2">Some text about me in culpa qui officia deserunt mollit anim..</p>
            <h3 className="mt-4">Some Links</h3>
            <p>Lorem ipsum dolor sit ame.</p>
            <ul className="navbar-nav">
            <li className="nav-item">
                <Link className="nav-link" to="/">Home</Link>
            </li>
            <li className="nav-item">
                <Link className="nav-link" to="/about">About</Link>
            </li>
            <li className="nav-item">
                <Link className="nav-link" to="/contact">Contact</Link>
            </li>
            </ul>
            <hr className="d-sm-none"/>
          </div>

          {/* ডান পাশের কলাম */}
          <div className="col-sm-8">
            <h2>TITLE HEADING</h2>
            <h5>Title description, Dec 7, 2020</h5>
            <div className="bg-secondary text-white text-center p-5 rounded mb-3">Fake Image</div>
            <p>Some text..</p>
            <p>Sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>

            <h2 className="mt-5">TITLE HEADING</h2>
            <h5>Title description, Sep 2, 2020</h5>
            <div className="bg-secondary text-white text-center p-5 rounded mb-3">Fake Image</div>
            <p>Some text..</p>
            <p>Sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>
          </div>
        </div>
      </div>
      
      <Footer />
    </>
  )
}
