import React from 'react'
import { Link } from 'react-router-dom'

export default function Nav() {
  return (
    <nav className="navbar navbar-expand-sm bg-dark navbar-dark sticky-top shadow-sm">
      <div className="container-fluid">
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
          <li className="nav-item">
            <Link className="nav-link" to="/props">Props</Link>
          </li>
          <li className="nav-item">
            <Link className="nav-link" to="/Events">Events</Link>
          </li>
          <li className="nav-item">
            <Link className="nav-link" to="/Lists">Lists</Link>
          </li>
          <li className="nav-item">
            <Link className="nav-link" to="/Forms">Forms</Link>
          </li>
          <li className="nav-item">
            <Link className="nav-link" to="/DisplayData">DisplayData</Link>
          </li>
          <li className="nav-item">
            <Link className="nav-link" to="/UserList">UserList</Link>
          </li>
        </ul>
      </div>
    </nav>
  )
}
